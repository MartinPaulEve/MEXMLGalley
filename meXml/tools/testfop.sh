#!/bin/bash

# Copyright (c) 2011 Martin Paul Eve
# Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.

saxon="./saxon9.jar"
javacmd="java -jar $saxon -o ../transform/debug/new.fo ../transform/debug/meXmlGalleySampleDocument.xml ../transform/jpub/jpub3-APAcit-xslfo.xsl"
fopcmd="fop -c ../transform/fop.xconf ../transform/debug/new.fo ../transform/debug/new.pdf"

if [ ! -f $saxon ];
then
    echo "ERROR: Unable to locate saxon9.jar. Please ensure that saxon9.jar is inside the meXml/tools directory."
    exit
fi

if [ ! -f ../transform/debug/meXmlGalleySampleDocument.xml ];
then
    echo "ERROR: Unable to locate ../transform/debug/meXmlGalleySampleDocument.xml. Please ensure you are running from inside the meXml/tools directory."
    exit
fi

if [ ! -f ../transform/jpub/jpub3-APAcit-xslfo.xsl ];
then
    echo "ERROR: Unable to locate ./transform/jpub/jpub3-APAcit-xslfo.xsl. Please ensure you are running from inside the meXml/tools directory."
    exit
fi

echo "INFO: Running saxon transform: $javacmd"
$javacmd

echo "INFO: Running FOP transform: $fopcmd"
$fopcmd
