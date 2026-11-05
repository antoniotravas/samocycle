#!/bin/sh
# Atualiza o site no servidor a partir do GitHub.
# O repositório git fica FORA da pasta pública (~/repos/samocycle.git),
# para que a pasta .git nunca seja servida pela web.
#
# Uso no servidor:  sh ~/domains/samocycle.espacodecodigo.eu/public_html/deploy.sh
set -e
GIT_DIR="${GIT_DIR:-$HOME/repos/samocycle.git}"
export GIT_DIR
git fetch --quiet origin
git reset --hard --quiet origin/main
echo "Atualizado para: $(git log -1 --format='%h %s (%cr)')"
