#!/usr/bin/env bash
# Cardino sunucu güvenliği: fail2ban (SSH) + otomatik güvenlik güncellemeleri.
# Docker kurallarıyla çakışmaması için ufw kurulmaz; dışarıya yalnızca 22/80/443 açık.
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive
apt-get install -y -q fail2ban python3-systemd unattended-upgrades >/dev/null

cat > /etc/fail2ban/jail.d/cardino.local <<'J'
[DEFAULT]
backend = systemd
bantime = 1h
bantime.increment = true
findtime = 10m
maxretry = 5

[sshd]
enabled = true
J
systemctl enable --now fail2ban
systemctl restart fail2ban

# Yalnızca güvenlik güncellemeleri, otomatik yeniden başlatma yok
cat > /etc/apt/apt.conf.d/20auto-upgrades <<'U'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
U
systemctl enable --now unattended-upgrades

sleep 2
fail2ban-client status sshd | sed -n '1,6p'
systemctl is-active unattended-upgrades
