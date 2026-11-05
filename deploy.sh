#!/bin/sh
# Atualiza o site no servidor a partir do GitHub.
# Uso (no servidor, dentro da pasta do site):  sh deploy.sh
set -e
cd "$(dirname "$0")"
git fetch --quiet origin
git reset --hard origin/main
echo "Atualizado para: $(git log -1 --format='%h %s (%cr)')"
