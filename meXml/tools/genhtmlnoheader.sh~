#!/bin/bash

# Copyright (c) 2011 Martin Paul Eve
# Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.

# determine the directory of the running script so we can find resources
SOURCE="${BASH_SOURCE[0]}"
DIR="$( dirname "$SOURCE" )"
while [ -h "$SOURCE" ]
do 
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE"
  DIR="$( cd -P "$( dirname "$SOURCE"  )" && pwd )"
done
scriptdir="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
saxon="$scriptdir/saxon9.jar"

# setup variables from input
infile=$1
filename=$(basename "$1")
filename=${filename%.*}

OUTFILE="./$(date +'%-m-%-e-%Y')-$filename.html"

javacmd="java -jar $saxon -o $OUTFILE.tmp $infile $scriptdir/../transform/jpub/jpub3-APAcit-html.xsl"

if [ ! -f $infile ];
then
    echo "ERROR: Unable to locate $infile."
    exit
fi

if [ ! -f $scriptdir/../transform/jpub/jpub3-APAcit-html.xsl ];
then
    echo "ERROR: Unable to locate $scriptdir/../transform/jpub/jpub3-APAcit-html.xsl."
    exit
fi

echo "INFO: Running saxon transform: $javacmd"
$javacmd

echo \<?xml version=\"1.0\" encoding=\"UTF-8\"?\>\<\!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" > $OUTFILE
echo \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"\> >> $OUTFILE
#echo \<html xmlns=\"http://www.w3.org/1999/xhtml\"\> >> $OUTFILE
cat "$OUTFILE.tmp" >> $OUTFILE
#echo \</html\> >> $OUTFILE

rm "$OUTFILE.tmp"
