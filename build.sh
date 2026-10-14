#!/bin/bash
#
# WordPress Plugin Distribution Builder
#
# This script creates a production-ready distribution zip file for WordPress.org submission.
# It automatically excludes development files using .distignore patterns.
#
# USAGE:
#   ./build.sh
#
# OUTPUT:
#   dist/<plugin-slug>.zip
#
# REQUIREMENTS:
#   - rsync (for efficient file copying with exclusion patterns)
#   - zip (for creating the distribution archive)
#   - bash 4+
#
# FEATURES:
#   - Automatically extracts plugin slug from Plugin Name header
#   - Idempotent: safe to run multiple times
#   - Respects .distignore patterns (similar to .gitignore)
#   - Cleans up temporary files automatically
#   - Color-coded output for easy reading
#
# NOTES:
#   - The .distignore file should list patterns to exclude from distribution
#   - Common exclusions: .git, node_modules, vendor, tests, *.log, etc.
#   - Created zip is ready for submission to WordPress.org
#   - Use this in CI/CD to automate release builds
#

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the directory of this script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# === STEP 1: Identify the plugin ===
# Find the main plugin file and extract the plugin slug from the header
PLUGIN_FILE=""
if [[ -f "${SCRIPT_DIR}/hihat-acf-metabox-ordering.php" ]]; then
    PLUGIN_FILE="${SCRIPT_DIR}/hihat-acf-metabox-ordering.php"
else
    # Find the first .php file with "Plugin Name:" header
    PLUGIN_FILE=$(grep -l "Plugin Name:" "${SCRIPT_DIR}"/*.php 2>/dev/null | head -1)
fi

# Extract plugin slug from Plugin Name header, or use directory name as fallback
# The slug is used for the zip filename and internal directory structure
if [[ -n "${PLUGIN_FILE}" ]] && [[ -f "${PLUGIN_FILE}" ]]; then
    PLUGIN_NAME=$(grep "^.*Plugin Name:" "${PLUGIN_FILE}" | sed 's/.*Plugin Name: \(.*\)/\1/' | sed 's/[[:space:]]*$//')
    # Convert plugin name to slug (lowercase, replace spaces with hyphens)
    PLUGIN_SLUG=$(echo "${PLUGIN_NAME}" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g' | sed 's/-+/-/g' | sed 's/^-\|-$//')
else
    PLUGIN_SLUG=$(basename "${SCRIPT_DIR}")
fi

echo -e "${YELLOW}Building distribution for: ${GREEN}${PLUGIN_SLUG}${NC}"

# === STEP 2: Setup build directories ===
# Create dist directory if it doesn't exist
DIST_DIR="${SCRIPT_DIR}/dist"
ZIP_FILE="${DIST_DIR}/${PLUGIN_SLUG}.zip"

# Completely clear the dist directory before building
if [[ -d "${DIST_DIR}" ]]; then
    echo -e "${YELLOW}Clearing previous builds...${NC}"
    rm -rf "${DIST_DIR}"
fi

mkdir -p "${DIST_DIR}"

# Create temp directory for build (automatically cleaned up on exit via trap)
BUILD_TEMP=$(mktemp -d)
trap "rm -rf '${BUILD_TEMP}'" EXIT

echo -e "${YELLOW}Creating build in: ${GREEN}${BUILD_TEMP}${NC}"

# === STEP 3: Validate .distignore ===
# Check if .distignore exists; if not, create a default one with common exclusions
if [[ ! -f "${SCRIPT_DIR}/.distignore" ]]; then
    echo -e "${RED}Warning: .distignore not found at ${SCRIPT_DIR}/.distignore${NC}"
    echo -e "${YELLOW}Creating default .distignore...${NC}"
    cat > "${SCRIPT_DIR}/.distignore" << 'EOF'
.git
.gitignore
.github
.gitattributes
node_modules
vendor
composer.json
composer.lock
package.json
package-lock.json
phpcs.xml
phpcs.xml.dist
phpstan.neon
phpstan.neon.dist
.phpcs.xml
tests
bin
.editorconfig
.distignore
.DS_Store
.vscode
.idea
*.log
*.tmp
*.bak
*.zip
EOF
    echo -e "${GREEN}Default .distignore created${NC}"
fi

# === STEP 4: Copy files with rsync ===
# rsync efficiently copies files while respecting .distignore exclusion patterns
# This ensures development files don't end up in the distribution
echo -e "${YELLOW}Copying files with rsync...${NC}"
rsync -av \
    --exclude-from="${SCRIPT_DIR}/.distignore" \
    "${SCRIPT_DIR}/" \
    "${BUILD_TEMP}/${PLUGIN_SLUG}/" \
    | grep -E "^(building|sent|total)" || true

# === STEP 5: Create the distribution zip ===
# Zip the temp directory; idempotent approach overwrites existing zip
echo -e "${YELLOW}Creating zip file...${NC}"
cd "${BUILD_TEMP}"
zip -r -q "${ZIP_FILE}" "${PLUGIN_SLUG}"

# === STEP 6: Report results ===
# Get file size for reference
ZIP_SIZE=$(du -h "${ZIP_FILE}" | cut -f1)

echo -e "${GREEN}✓ Build complete!${NC}"
echo -e "${GREEN}Zip file: ${ZIP_FILE}${NC}"
echo -e "${GREEN}Size: ${ZIP_SIZE}${NC}"
echo -e "${YELLOW}Next steps:${NC}"
echo -e "  1. Test the plugin by extracting: unzip -l '${ZIP_FILE}'"
echo -e "  2. Upload to WordPress.org plugin repository"
echo -e "  3. Verify all necessary files are included and development files are excluded"
