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
saxon="$scriptdir/../runtime/saxon9.jar:$scriptdir/../runtime/xml-resolver-1.1.jar"

# setup variables from input
infile=$1
filename=$(basename "$1")
filename=${filename%.*}

# construct commands
#javacmd="java -jar $saxon -o $scriptdir/../transform/debug/new.fo $infile $scriptdir/../transform/jpub/jpub3-APAcit-xslfo.xsl logo=$scriptdir/../transform/resources/logo.jpg"
fopcmd="fop -q -c $scriptdir/../transform/fop.xconf $scriptdir/../transform/debug/new.fo ./$(date +'%-m-%-e-%Y')-$filename.pdf"


if [ ! -f $infile ];
then
    echo "ERROR: Input file $1 not found."
    exit
fi

if [ ! -f $scriptdir/../transform/jpub/jpub3-APAcit-xslfo.xsl ];
then
    echo "ERROR: Unable to locate $scriptdir/../transform/jpub/jpub3-APAcit-xslfo.xsl."
    exit
fi

echo "INFO: Running saxon transform: $javacmd"
#$javacmd
java -classpath "$saxon" -Dxml.catalog.files="$scriptdir/../runtime/catalog.xml" net.sf.saxon.Transform -x org.apache.xml.resolver.tools.ResolvingXMLReader -y org.apache.xml.resolver.tools.ResolvingXMLReader -r org.apache.xml.resolver.tools.CatalogResolver -o "$scriptdir/../transform/debug/new.fo" "$infile" "$scriptdir/../transform/jpub/jpub3-APAcit-xslfo.xsl" logo="$scriptdir/../transform/resources/logo.jpg"

echo "INFO: Running FOP transform: $fopcmd"
$fopcmd
