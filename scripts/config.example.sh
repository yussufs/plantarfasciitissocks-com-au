# Copy to scripts/config.sh and fill in values.
# config.sh is gitignored.

BRAND="brand-alpha"
DOMAIN="brand-alpha.com"
LOCAL_URL="http://brand-alpha.test"
LOCAL_PATH="$HOME/Sites/brand-alpha"
ADMIN_EMAIL="admin@brand-alpha.com"
BRAND_COLOR="#0ea5e9"

# Local admin used by pull-db.sh (so you never need production credentials).
# Defaults to admin/admin if omitted.
LOCAL_ADMIN_USER="admin"
LOCAL_ADMIN_PASS="admin"

# Hostinger SSH
SSH_HOST=""
SSH_USER=""
SSH_PORT="65002"
SSH_KEY=""
# IMPORTANT: ~/public_html is the account's PRIMARY domain. For a secondary/
# addon domain (the usual case for a brand fork) point this at that site's
# directory, e.g. ~/domains/brand-alpha.com/public_html — otherwise pull-db.sh
# will export the wrong site. (pull-db.sh now verifies this against DOMAIN.)
SSH_PATH="~/domains/brand-alpha.com/public_html"
# Remote theme directory rsync target (deploy.sh).
REMOTE_PATH="~/domains/brand-alpha.com/public_html/wp-content/themes/brand-alpha"

# Future orchestrator
ORCHESTRATOR_URL="https://orchestrator.yourdomain.com"
WEBHOOK_SECRET=""

# Amazon SES (FluentSMTP)
SES_ACCESS_KEY=""
SES_SECRET_KEY=""
SES_REGION="ap-southeast-2"
