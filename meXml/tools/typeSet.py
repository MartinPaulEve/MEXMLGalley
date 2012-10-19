#!/usr/bin/env python

"""Script to parse endnoted outputs from http://xing.github.com/wysihtml5/ to reduce typesetting work."""

# Copyright (c) 2012 Martin Paul Eve
# Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.

import os
import re
import sys

__author__ = "Martin Paul Eve"
__copyright__ = "Copyright 2012"
__credits__ = ["Martin Paul Eve"]
__license__ = "GPL"
__version__ = "2"
__maintainer__ = "Martin Paul Eve"
__email__ = "martin@martineve.com"
__status__ = "Production"


numeral_map = zip(
    (1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1),
    ('M', 'CM', 'D', 'CD', 'C', 'XC', 'L', 'XL', 'X', 'IX', 'V', 'IV', 'I')
)

def int_to_roman(i):
    result = []
    for integer, numeral in numeral_map:
        count = int(i / integer)
        result.append(numeral * count)
        i -= integer * count
    return ''.join(result)

def roman_to_int(n):
    n = unicode(n).upper()

    i = result = 0
    for integer, numeral in numeral_map:
        while n[i:i + len(numeral)] == numeral:
            result += integer
            i += len(numeral)
    return result

if __name__ == "__main__":

	with open(sys.argv[1], 'r') as myFile:
		data = myFile.read()

		data = re.sub("\s\s+" , " ", data)	
		
		data = data.replace("\n", " ")
		data = data.replace("</p>", "</p>\n")
		
		data = data.replace('<p class="wysiwyg-text-align-justify"><br> </p>', '')
		data = data.replace(' class="wysiwyg-text-align-justify"', '')
		data = data.replace('<br>', '')
		data = data.replace('\n \n', '\n')
		data = data.replace('\n\n', '\n')
		data = data.replace(' <p', '<p')
		data = data.replace('<i>', '<italic>')
		data = data.replace('</i>', '</italic>')

		# FOOTNOTE CONVERSION
		# Stage 1: Convert links
		# Convert <a target="_blank" rel="nofollow">xiv</a> to <fn-link id="xr6" href="fn6"><sup>6</sup></fn-link>
		pattern = re.compile(r'\<a target=\"_blank\" rel=\"nofollow\"\>(.+?)\<\/a\>')
		result = pattern.finditer(data)

		if result:
			for match in result:
				# match.group(0) contains: the whole string
				# match.group(1) contains: the footnote number
				fn_number = str(roman_to_int(match.group(1)))
				data = data.replace(match.group(0), '<fn-link id="xr' + fn_number + '" href="fn' + fn_number + '"><sup>' + fn_number + '</sup></fn-link>')
				#print 'Replace: ' + match.group(0)
				#print 'With: ' + '<fn-link id="xr' + fn_number + '" href="fn' + fn_number + '"><sup>' + fn_number + '</sup></fn-link>'

		# Stage 2: Reconvert the note itself
		# Convert <p><fn-link id="xr2" href="fn2"><sup>2</sup></fn-link><span class="wysiwyg-font-size-small"> 	Ibid: 37. </span> 	</p> to <fn><label><fn-link href="xr1" id="fn1">1</fn-link></label><p>Hayles, p. 25.</p></fn>
		pattern = re.compile(r'\<p\>\<fn-link id=\"xr(\d+)\" href=\"fn\d+\"\>\<sup\>\d+\<\/sup\>\<\/fn-link\>\s*\<span class=\"wysiwyg-font-size-small\"\>\s*(.+?)\s*\<\/span\>\s*\<\/p\>')

		result = pattern.finditer(data)

		if result:
			for match in result:
				# match.group(0) contains: the whole string
				# match.group(1) contains: the footnote number
				# match.group(2) contains: the footnote text
				fn_number = match.group(1)
				data = data.replace(match.group(0), '<fn><label><fn-link href="xr' + fn_number + '" id="fn' + fn_number + '">' + fn_number + '</fn-link></label><p>' + match.group(2) + '</p></fn>\n')
				#print 'Replace: ' + match.group(0)
				#print 'With: ' + '<fn-link id="xr' + fn_number + '" href="fn' + fn_number + '"><sup>' + fn_number + '</sup></fn-link>'

		data = data.replace("<div>", "")
		data = data.replace("</div>", "")
		data = re.sub("\s\s+" , " ", data)
		data = data.replace("</fn>", "</fn>\n")
		data = data.replace('<p> </p>', '')
		data = data.replace('<p></p>', '')
		data = data.replace('\n\n', '\n')
		print data;
