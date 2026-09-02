# Marvel

A complete ecommerce `api` solution with both `REST` and `GraphQL` support.

## Media upload limits

Product video uploads allow files up to **50MB** (`video/mp4`, `video/webm`, `video/quicktime`). Spatie Media Library `max_file_size` is set to 50MB in `config/media-library.php`.

Ensure the runtime also allows large uploads:

- PHP: `upload_max_filesize` and `post_max_size` ≥ `50M`
- Nginx (if used): `client_max_body_size` ≥ `50m`
- Apache (if used): `LimitRequestBody` high enough for 50MB

Without these, video uploads may fail with HTTP 413 or empty request bodies.

