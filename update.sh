git fetch origin
git reset --hard origin/main
composer install
npm install
npm run build

current_timestamp=`date +%Y%m%d.%H%M%S`
sed -i "s/VERSION =.*/VERSION = '${current_timestamp}';/g" public/serviceworker.js
