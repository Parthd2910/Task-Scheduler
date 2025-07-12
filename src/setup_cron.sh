PHP_BIN=$(which php)

SCRIPT_PATH="$(cd "$(dirname "$0")" && pwd)/cron.php"

CRON_JOB="* * * * * $PHP_BIN $SCRIPT_PATH"

echo "PHP found at: $PHP_BIN"
echo "cron.php path: $SCRIPT_PATH"
echo "Adding this cron job:"
echo "$CRON_JOB"

(crontab -l 2>/dev/null | grep -Fv "$SCRIPT_PATH"; echo "$CRON_JOB") | sort -u | crontab -

echo "Current crontab entries:"
crontab -l
