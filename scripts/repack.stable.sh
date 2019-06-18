#! /bin/bash

inotifywait -r -e modify,create,delete "/var/www/miranda-ng.org/htdocs/distr/stable/timestamp.chk"

echo "repack!"
bash /var/www/miranda-ng.org/scripts/miranda-ng-pdb.stable.sh
