#!/usr/bin/env bash
# Tek seferlik: sunucudaki Laravel kodunu GitHub deposuyla birleştirir.
set -euo pipefail
APP=/var/www/cardino/app
mkdir -p /root/.ssh
grep -q 'cardino_github' /root/.ssh/config 2>/dev/null || printf 'Host github.com\n    IdentityFile /root/.ssh/cardino_github\n    IdentitiesOnly yes\n' >> /root/.ssh/config
grep -q '^github.com' /root/.ssh/known_hosts 2>/dev/null || ssh-keyscan -t ed25519 github.com >> /root/.ssh/known_hosts 2>/dev/null
git config --global --get-all safe.directory | grep -qx "$APP" || git config --global --add safe.directory "$APP"
cd "$APP"
if [ ! -d .git ]; then
    git init -q -b main
    git remote add origin git@github.com:anketci54-coder/cardino.git
fi
git fetch -q origin main
git reset -q origin/main
git checkout origin/main -- README.md CLAUDE.md docs deploy
for l in '.env' '*.log' '/storage/*.key'; do grep -qxF "$l" .gitignore || echo "$l" >> .gitignore; done
git add -A
git -c user.name="Kürşat Akyol" -c user.email="kursat_akyol@hotmail.com" commit -q -F - <<'MSG'
Laravel 13 uygulaması (sunucudaki kurulum)

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01GFkj5dtiHpEXP4DCnAFvHm
MSG
git push -q origin main
install -m 755 deploy/cardino-guncelle /usr/local/bin/cardino-guncelle
chown -R www-data:www-data "$APP"
git log --oneline -3
git status --short | head
echo ESITLENDI
