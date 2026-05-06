#!/usr/bin/env bash

REPO_URL="git@github.com:PetpowerSAS/petpower-api-gateway.git"
REPO_BRANCH="stage"
WEB_ROOT="/home/petpower/web/api.stage.petpower.ec"
APP_DIR=$WEB_ROOT/private
CURRENT_DIR=$APP_DIR/current
RELEASES_DIR=$APP_DIR/releases
TEMP_DIR=$APP_DIR/tmp
PUBLIC_DIR=$WEB_ROOT/public_html


echo ">>> Repo URL:		$REPO_URL"
echo ">>> Repo branch:	$REPO_BRANCH"
echo ">>> Web root:		$WEB_ROOT"
echo ">>> App:		$APP_DIR"
echo ">>> Current:		$CURRENT_DIR"
echo ">>> Releases:		$RELEASES_DIR"
echo ">>> Temp:		$TEMP_DIR"
echo ">>> Public Dir:		$PUBLIC_DIR"

if [[ -d "$TEMP_DIR/.git" ]]; then
	rm -rf $TEMP_DIR
fi
[ -d $TEMP_DIR ] || mkdir -p $TEMP_DIR
[ -d $RELEASES_DIR ] || mkdir -p $RELEASES_DIR

echo ">>> Cloning repository"
git clone --branch $REPO_BRANCH --depth 1 $REPO_URL $TEMP_DIR
if [[ -d "$TEMP_DIR/.git" ]]; then
	echo ">>> Finished cloning repository"
	cd $TEMP_DIR
	COMMIT_SHA="$(git rev-parse --short HEAD)"
	RELEASE_DATE=$(date +%Y%m%d%H%M%S)
	RELEASE_CODE=$RELEASE_DATE"_"$COMMIT_SHA
	NEW_RELEASE_DIR=$RELEASES_DIR/$RELEASE_CODE
	
	echo 'Linking .env file'
	ln -nfs $APP_DIR/.env $TEMP_DIR/.env
	
	echo 'Linking storage directory'
	rm -rf $TEMP_DIR/storage
	ln -nfs $APP_DIR/storage $TEMP_DIR/storage
	
	echo 'Starting install'
	composer install
	
	echo 'Migrating DB'
	php artisan migrate --force
	
	echo 'Running NPM stuff'
	npm install
	npm run build
		
	cd ../
	mv $TEMP_DIR $NEW_RELEASE_DIR
	
	echo 'Linking current release'
	echo $NEW_RELEASE_DIR
	echo $CURRENT_DIR
	ln -nfs $NEW_RELEASE_DIR $CURRENT_DIR
	
	echo 'Linking public storage'
	cd $NEW_RELEASE_DIR
	php artisan storage:link
	
	echo 'Linking public dirs ('$CURRENT_DIR'/public to '$PUBLIC_DIR')'
	[ -d $PUB_DIR ] || rm -rf $PUBLIC_DIR/*
	ln -nfs $CURRENT_DIR/public/* $PUBLIC_DIR
	ln -nfs $CURRENT_DIR/public/.htaccess $PUBLIC_DIR
	
	echo 'Keeping Only the last 3 deployments'
	cd $RELEASES_DIR 
	ls -t1 $RELEASES_DIR | tail -n +4 | xargs rm -rf
else
	echo ">>> FAILED TO CLONE..."
	exit
fi
