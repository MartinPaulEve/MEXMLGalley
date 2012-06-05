#!/bin/bash

# Copyright (c) 2011 Martin Paul Eve
# Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.

saxon="./saxon9.jar"
javacmd="java -jar $saxon -o ../transform/debug/new.html ../transform/debug/meXmlGalleySampleDocument.xml ../transform/jpub/jpub3-APAcit-html.xsl"

if [ ! -f ../transform/debug/meXmlGalleySampleDocument.xml ];
then
    echo "ERROR: Unable to locate ../transform/debug/meXmlGalleySampleDocument.xml. Please ensure you are running from inside the meXml/tools directory."
    exit
fi

if [ ! -f ../transform/jpub/jpub3-APAcit-html.xsl ];
then
    echo "ERROR: Unable to locate ./transform/jpub/jpub3-APAcit-html.xsl. Please ensure you are running from inside the meXml/tools directory."
    exit
fi

echo "INFO: Running saxon transform: $javacmd"
$javacmd

