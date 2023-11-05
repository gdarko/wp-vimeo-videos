#!/bin/bash

SCRIPT_DIR="$(
  cd -- "$(dirname "$0")" >/dev/null 2>&1
  pwd -P
)"
PLUGIN_DIR="$(
  cd -- "$(dirname "$SCRIPT_DIR")" >/dev/null 2>&1
  pwd -P
)"
PLUGINS_ROOT_DIR="$(
  cd -- "$(dirname "$PLUGIN_DIR")" >/dev/null 2>&1
  pwd -P
)"
PLUGIN_SLUG=$(basename $PLUGIN_DIR)

cd $PLUGIN_DIR

if [[ -f "$PLUGIN_DIR/composer.json" ]]; then
  rm -rf "$PLUGIN_DIR/vendor"
  composer install --no-dev
fi

if [ -f "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip" ]; then
  rm "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip"
fi

cd "$PLUGIN_DIR/blocks"
rm -rf node_modules
rm -rf package-lock.json
npm install --legacy-peer-deps
cd "$PLUGIN_DIR"
rm -rf node_modules
rm -rf package-lock.json
npm install --legacy-peer-deps
npm run prod

if [[ ! -d "$PLUGIN_DIR/blocks/dist" || ! -d "$PLUGIN_DIR/assets/admin/dist" ]]; then
  echo "Unable to build with NPM"
  exit
fi

cd "$PLUGINS_ROOT_DIR"

zip -r "$PLUGIN_SLUG.zip" "$PLUGIN_SLUG" \
  -x="*scripts*" \
  -x="*assets/admin/src*" \
  -x="*assets/shared/src*" \
  -x="*assets/frontend/src*" \
  -x="*blocks/src*" \
  -x="*blocks/node_modules*" \
  -x="*blocks/package.json*" \
  -x="*blocks/package-lock.json*" \
  -x="*.git*" \
  -x="*package.json*" \
  -x="*package-lock.json*" \
  -x="*gulpfile.js*" \
  -x="*composer.json*" \
  -x="*composer.lock*" \
  -x="*README.md*"

echo "New version ready."
