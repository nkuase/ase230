#!/usr/bin/env bash
# Prepare a Hugo source repository for GitHub Pages deployment.
#
# Run this script from inside 3_Github_io_Hugo_Deployment/.
# The Hugo source is its sibling: ../my-portfolio/.
#
# Usage:
#   cd 3_Github_io_Hugo_Deployment
#   ./setup.sh ../yourusername.github.io
#
# The script copies the Hugo source and Pages workflow into the repository. It
# never commits, pushes, or deletes files. Check `git status` before committing.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SITE_DIR="$SCRIPT_DIR/../my-portfolio"
PAGES_DIR_ARG="${1:-}"

usage() {
  echo "Usage: $0 ../yourusername.github.io" >&2
  echo "Run it from inside 3_Github_io_Hugo_Deployment/." >&2
  exit 1
}

[[ -n "$PAGES_DIR_ARG" && "$#" -eq 1 ]] || usage
[[ -d "$PAGES_DIR_ARG" ]] || {
  echo "Pages repository directory not found: $PAGES_DIR_ARG" >&2
  exit 1
}

PAGES_DIR="$(cd "$PAGES_DIR_ARG" && pwd -P)"
PAGES_DIR_NAME="${PAGES_DIR##*/}"
WORKFLOW_SOURCE="$SCRIPT_DIR/workflows/hugo.yml"
GITIGNORE_SOURCE="$SCRIPT_DIR/gitignore"

for required in "$SITE_DIR/hugo.toml" "$PAGES_DIR/.git" "$WORKFLOW_SOURCE" "$GITIGNORE_SOURCE"; do
  if [[ ! -e "$required" ]]; then
    echo "Required path not found: $required" >&2
    exit 1
  fi
done

if [[ "$PAGES_DIR_NAME" != *.github.io ]]; then
  echo "Warning: '$PAGES_DIR_NAME' does not end with .github.io." >&2
  echo "For a personal Pages site, use yourusername.github.io." >&2
fi

# Refuse to overwrite a source project or an existing deployment workflow.
for item in content layouts static archetypes hugo.toml .github/workflows/hugo.yml; do
  if [[ -e "$PAGES_DIR/$item" ]]; then
    echo "Refusing to overwrite existing path: $PAGES_DIR/$item" >&2
    echo "Start with a new Pages repository, or copy the needed files yourself." >&2
    exit 1
  fi
done

echo "Copying Hugo source into $PAGES_DIR_NAME ..."
cp -R "$SITE_DIR/content" "$SITE_DIR/layouts" "$SITE_DIR/static" "$SITE_DIR/archetypes" "$SITE_DIR/hugo.toml" "$PAGES_DIR/"

mkdir -p "$PAGES_DIR/.github/workflows"
cp "$WORKFLOW_SOURCE" "$PAGES_DIR/.github/workflows/hugo.yml"

# Preserve an existing .gitignore while adding the supplied ignore rules.
touch "$PAGES_DIR/.gitignore"
while IFS= read -r rule; do
  [[ -z "$rule" || "$rule" == \#* ]] && continue
  if ! grep -Fqx "$rule" "$PAGES_DIR/.gitignore"; then
    printf '%s\n' "$rule" >> "$PAGES_DIR/.gitignore"
  fi
done < "$GITIGNORE_SOURCE"

cat <<EOF

Prepared $PAGES_DIR_NAME for GitHub Pages.

Next steps:
  1. Edit $PAGES_DIR_NAME/hugo.toml (name, links, and local baseURL).
  2. In GitHub: Settings > Pages > Source > GitHub Actions.
  3. Open $PAGES_DIR_NAME in VS Code, check Source Control, then commit and sync.

The workflow builds public/ on GitHub. Do not commit generated public/ files.
EOF
