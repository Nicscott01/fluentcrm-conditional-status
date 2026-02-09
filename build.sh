#!/bin/bash

# Build script for FluentCRM Conditional Status plugin
# This creates a distributable ZIP file

PLUGIN_SLUG="fluentcrm-conditional-status"
VERSION=$(grep "Version:" fluentcrm-conditional-status.php | awk '{print $3}')
BUILD_DIR="build"
DIST_DIR="dist"

echo "Building $PLUGIN_SLUG version $VERSION..."

# Clean up old builds
rm -rf $BUILD_DIR
rm -rf $DIST_DIR
mkdir -p $BUILD_DIR/$PLUGIN_SLUG
mkdir -p $DIST_DIR

# Copy plugin files
echo "Copying plugin files..."
rsync -av --exclude-from='.distignore' \
  --exclude='build' \
  --exclude='dist' \
  --exclude='.git' \
  --exclude='.github' \
  . $BUILD_DIR/$PLUGIN_SLUG/

# Create ZIP file
echo "Creating ZIP file..."
cd $BUILD_DIR
zip -r ../$DIST_DIR/$PLUGIN_SLUG-$VERSION.zip $PLUGIN_SLUG
cd ..

# Clean up build directory
rm -rf $BUILD_DIR

echo "Build complete! Package created at: $DIST_DIR/$PLUGIN_SLUG-$VERSION.zip"
