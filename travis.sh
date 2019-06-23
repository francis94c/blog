# ENV
SPLINT_VERSION="0.0.3"
VENDOR="francis94c"
PACKAGE="blog"

# Begin
created=false

for entry in ./*
do
	if [ $created = false ]; then
		mkdir -p travis-splint-$SPLINT_VERSION/application/splints/
		$created = true
	fi
	if [ "x$entry" != "x./$VENDOR/$PACKAGE/phpunit.xml" ] && [ "x$entry" != "x./$VENDOR/$PACKAGE/travis.sh" ]; then
		echo $entry
		cp -r $entry travis-splint-$SPLINT_VERSION/application/splints/
		rm -rf $entry
	fi
done

cp "$VENDOR/$PACKAGE/phpunit.xml" "../../phpunit.xml"

wget https://github.com/splintci/travis-splint/archive/v$SPLINT_VERSION.tar.gz -O - | tar xz

# Dependencies

wget https://github.com/francis94c/ci-parsedown/archive/v0.0.2.tar.gz -O - | tar xz

mv ci-parsedown-0.0.2 ci-parsedown
mkdir -p travis-splint-$SPLINT_VERSION/application/splints/francis94c/
cp -r ci-parsedown travis-splint-$SPLINT_VERSION/application/splints/francis94c/
rm -rf ci-parsedown
