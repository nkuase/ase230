#!/usr/bin/env bash
# Build and check the Hugo portfolio example.
#
# Usage:
#   ./run.sh          Build with `hugo --minify`, then check the output
#   ./run.sh serve    Preview at http://localhost:1313 (drafts included)
#   ./run.sh clean    Remove generated files (public/, resources/)

set -euo pipefail

# Always work inside my-portfolio/, no matter where the script is called from.
SITE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/my-portfolio"
cd "$SITE_DIR"

if ! command -v hugo >/dev/null 2>&1; then
  echo "Hugo is not installed. Follow 'Hugo Installation' first." >&2
  exit 1
fi

check_output() {
  # Pages and assets that a correct build must produce.
  local expected=(
    public/index.html
    public/about/index.html
    public/projects/index.html
    public/projects/student-api/index.html
    public/posts/index.html
    public/posts/getting-started-with-hugo/index.html
    public/css/style.css
    public/images/student-api.png
  )
  local missing=0
  for f in "${expected[@]}"; do
    if [[ -f "$f" ]]; then
      echo "  ok       $f"
    else
      echo "  MISSING  $f (is the page still a draft?)"
      missing=1
    fi
  done
  return $missing
}

case "${1:-build}" in
  build)
    hugo version
    echo "Building (drafts excluded, like the real deployment)..."
    hugo --minify --cleanDestinationDir
    echo "Checking public/ ..."
    check_output
    echo "Done. The site is in: $SITE_DIR/public"
    echo "Tip: run './run.sh serve' to view it in a browser."
    ;;
  serve)
    hugo version
    exec hugo server -D
    ;;
  clean)
    rm -rf public resources .hugo_build.lock
    echo "Removed public/, resources/, .hugo_build.lock"
    ;;
  *)
    echo "Usage: $0 [build|serve|clean]" >&2
    exit 1
    ;;
esac
