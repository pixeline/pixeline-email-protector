#! /bin/bash
# A modification of Dean Clatworthy's deploy script: https://github.com/deanc/wordpress-plugin-git-svn

set -euo pipefail

# main config
PLUGINSLUG="pixelines-email-protector"
CURRENTDIR=`pwd`
# this should be the name of your main php file in the wordpress plugin
MAINFILE="pixeline-email-protector.php"


# git config
# this file should be in the base of your git repository
GITPATH="$CURRENTDIR/"

# svn config

# path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNPATH="/tmp/$PLUGINSLUG"

# Remote SVN repo on wordpress.org, with trailing slash
SVNURL="https://plugins.svn.wordpress.org/$PLUGINSLUG/"
# your svn username
SVNUSER="pixeline"


# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy wordpress plugin"
echo
echo ".........................................."
echo

# Check if subversion is installed before getting all worked up
if ! which svn >/dev/null; then
	echo "You'll need to install subversion before proceeding. Exiting....";
	exit 1;
fi

# cleanup last deploy
rm -fr "$SVNPATH/"

# Check version in readme.txt is the same as plugin file after translating both to unix line breaks to work around grep's failure to identify mac line breaks
NEWVERSION1=`grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}'`
echo "readme.txt version: $NEWVERSION1"
NEWVERSION2=`grep "^Version:" $GITPATH/$MAINFILE | awk -F' ' '{print $NF}'`
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" != "$NEWVERSION2" ]; then echo "Version in readme.txt & $MAINFILE don't match. Exiting...."; exit 1; fi

echo "Versions match in readme.txt and $MAINFILE. Let's proceed..."

echo "Checking remote SVN repository exists..."
if ! svn ls "$SVNURL" >/dev/null 2>&1; then
	echo "SVN repository URL not found: $SVNURL"
	echo "Please verify PLUGINSLUG and plugin ownership on WordPress.org. Exiting...."
	exit 1
fi

echo "SVN repository found. Continuing..."

# if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION1"
# 	then
# 		echo "Version $NEWVERSION1 already exists as git tag. Exiting....";
# 		exit 1;
# 	else
# 		echo "Git version does not exist. Let's proceed..."
# fi

cd "$GITPATH"
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "master" ]; then
	echo "Current git branch is '$CURRENT_BRANCH'. Deploy must run from 'master'. Exiting...."
	exit 1
fi

echo -e "Enter a commit message for this new version: \c"
read COMMITMSG

if [ -n "$(git status --porcelain)" ]; then
	git commit -am "$COMMITMSG"
else
	echo "No local changes to commit. Skipping git commit."
fi

echo "Tagging new version in git"
if git rev-parse -q --verify "refs/tags/$NEWVERSION1" >/dev/null; then
	echo "Git tag $NEWVERSION1 already exists. Skipping tag creation."
else
	git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"
fi

echo "Pushing latest commit to origin, with tags"
git push origin master
git push origin master --tags

echo
echo "Creating local copy of SVN repo ..."
svn co "$SVNURL" "$SVNPATH"

echo "Clearing svn repo so we can overwrite it"
svn rm "$SVNPATH"/trunk/*

echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix="$SVNPATH"/trunk/

if [ ! -f "$SVNPATH/trunk/readme.txt" ]; then
	echo "Missing readme.txt in SVN trunk export. Exiting...."
	exit 1
fi

echo "Ignoring github specific files and deployment script"
svn propset svn:ignore "deploy.sh
README.md
.git
.gitignore" "$SVNPATH/trunk/"

echo "Moving assets-wp-repo"
mkdir -p "$SVNPATH"/assets/
if [ -d "$SVNPATH/trunk/assets-wp-repo" ]; then
	if compgen -G "$SVNPATH/trunk/assets-wp-repo/*" > /dev/null; then
		mv "$SVNPATH"/trunk/assets-wp-repo/* "$SVNPATH"/assets/
	fi
	svn add --force "$SVNPATH"/assets/ >/dev/null 2>&1 || true
	svn delete --force "$SVNPATH"/trunk/assets-wp-repo >/dev/null 2>&1 || true
else
	echo "No assets-wp-repo directory found in trunk export. Skipping asset move."
fi


echo "Changing directory to SVN and committing to trunk"
cd "$SVNPATH"/trunk/
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add

if ! svn ls | grep -q '^readme\.txt$'; then
	echo "SVN trunk is missing readme.txt after export/add. Exiting...."
	exit 1
fi

echo "committing to trunk"
svn commit --username=$SVNUSER -m "$COMMITMSG"

echo "Updating WP plugin repo assets & committing"
cd "$SVNPATH"/assets/
svn commit --username=$SVNUSER -m "Updating wp-repo-assets"

echo "Creating new SVN tag & committing it"
cd "$SVNPATH"
svn copy trunk/ tags/$NEWVERSION1/

if ! svn ls "$SVNPATH/tags/$NEWVERSION1" | grep -q '^readme\.txt$'; then
	echo "Tag source is missing readme.txt. Exiting...."
	exit 1
fi

cd "$SVNPATH"/tags/"$NEWVERSION1"
svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1"

echo "Removing temporary directory $SVNPATH"
rm -fr "$SVNPATH/"

echo "*** FIN ***"