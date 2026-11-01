#!/usr/bin/env bash
# Signs a local sample image the same way Turahe\Core\Services\Image\ImageSignature does
# (HMAC-SHA256 of salt + path, base64url, no padding) and requests it from ImgProxy.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$root"

if [[ -f .env ]]; then
    set -a
    # shellcheck disable=SC1091
    source .env
    set +a
fi

port="${IMGPROXY_PORT:-8080}"
base="http://127.0.0.1:${port}"
key="${IMGPROXY_KEY:-0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef}"
salt="${IMGPROXY_SALT:-0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef}"

echo "Waiting for ImgProxy at ${base}/health"
ready=0
for _ in $(seq 1 30); do
    if curl -fsS "${base}/health" >/dev/null 2>&1; then
        ready=1
        break
    fi
    sleep 1
done

if [[ "$ready" -ne 1 ]]; then
    echo "ImgProxy did not become healthy at ${base}/health" >&2
    exit 1
fi

echo "Health check passed"

encode_url() {
    python3 -c 'import base64, sys; print(base64.urlsafe_b64encode(sys.argv[1].encode()).decode().rstrip("="))' "$1"
}

sign_path() {
    python3 - "$1" "$key" "$salt" <<'PY'
import base64
import hashlib
import hmac
import sys

path, key_hex, salt_hex = sys.argv[1], sys.argv[2], sys.argv[3]
digest = hmac.new(bytes.fromhex(key_hex), bytes.fromhex(salt_hex) + path.encode(), hashlib.sha256).digest()
print(base64.urlsafe_b64encode(digest).decode().rstrip("="))
PY
}

request_image() {
    local path="$1"
    local outfile="$2"
    local signature url code

    signature="$(sign_path "$path")"
    url="${base}/${signature}${path}"
    code="$(curl -sS -o "$outfile" -D "${outfile}.headers" -w "%{http_code}" "$url")"
    if [[ "$code" != "200" ]]; then
        echo "Expected HTTP 200 for ${path}, got ${code}" >&2
        echo "URL: ${url}" >&2
        cat "${outfile}" >&2 || true
        exit 1
    fi

    local content_type
    content_type="$(awk 'BEGIN {IGNORECASE=1} /^content-type:/ {print $2}' "${outfile}.headers" | tr -d '\r')"
    case "$content_type" in
        image/*) ;;
        *)
            echo "Expected an image response for ${path}, got Content-Type '${content_type}'" >&2
            exit 1
            ;;
    esac

    local magic
    magic="$(od -An -t x1 -N 4 "$outfile" | tr -d ' \n')"
    if [[ "$magic" != "89504e47" ]]; then
        echo "Expected PNG bytes for ${path}, got magic ${magic}" >&2
        exit 1
    fi

    echo "OK ${path} -> ${content_type} ($(wc -c < "$outfile") bytes)"
}

source_url="$(encode_url "local:///sample.png")"
request_image "/rs:fit:64:64:0/g:no/${source_url}.png" /tmp/turahe-imgproxy-resize.png
# ImgProxy v4 selects a named preset with the pr: option.
request_image "/pr:thumbnail/${source_url}.png" /tmp/turahe-imgproxy-preset.png

bad_code="$(curl -sS -o /dev/null -w "%{http_code}" "${base}/invalid-signature/rs:fit:64:64:0/g:no/${source_url}.png")"
if [[ "$bad_code" == "200" ]]; then
    echo "Unsigned or badly signed URL was accepted" >&2
    exit 1
fi
echo "Rejected bad signature with HTTP ${bad_code}"

echo "ImgProxy docker compose test passed"
