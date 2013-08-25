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

# construct commands
fopcmd="$scriptdir/genfop.sh $infile"
htmlcmd="$scriptdir/genhtmlnoheader.sh $infile"


if [ ! -f $infile ];
then
    echo "ERROR: Input file $1 not found."
    exit
fi

echo "INFO: Running PDF transform: $fopcmd"
$fopcmd

echo "INFO: Running HTML transform: $fopcmd"
$htmlcmd
