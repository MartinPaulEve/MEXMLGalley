#!/bin/bash

# Copyright (c) 2011 Martin Paul Eve
# Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.

saxon=`locate -l1 saxon9.jar`
javacmd="java -jar $saxon -o ./transform/debug/new.fo ./transform/meXmlGalleySampleDocument.xml ./transform/jpub/jpub3-APAcit-xslfo.xsl"
fopcmd="fop ./transform/debug/new.fo ./transform/debug/new.pdf"

if [ ! -f $saxon ];
then
    echo "ERROR: Unable to locate saxon9.jar. Please ensure that \"locate\" can find this file."
    exit
fi

if [ ! -f ./transform/meXmlGalleySampleDocument.xml ];
then
    echo "ERROR: Unable to locate ./transform/meXmlGalleySampleDocument.xml. Please ensure you are running from inside the meXml directory."
    exit
fi

if [ ! -f ./transform/jpub/jpub3-APAcit-xslfo.xsl ];
then
    echo "ERROR: Unable to locate ./transform/jpub/jpub3-APAcit-xslfo.xsl. Please ensure you are running from inside the meXml directory."
    exit
fi

echo "INFO: Running saxon transform: $javacmd"
$javacmd

echo "INFO: Running FOP transform: $fopcmd"
$fopcmd
