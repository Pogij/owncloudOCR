#!/bin/sh
##############################################################################
# Script aimed at OCRing a single page of a PDF file
#
# Copyright (c) 2013-14: fritz-hh from Github (https://github.com/fritz-hh)
##############################################################################

. "./src/config.sh"


# Initialization of variables passed by arguments
FILE_INPUT_PDF="$1"			# PDF file containing the page to be OCRed
PAGE_INFO="$2"				# Various characteristics of the page to be OCRed
NUM_PAGES="$3"				# Total number of page of the PDF file (required for logging)
TMP_FLD="$4"				# Folder where the temporary files should be placed
VERBOSITY="$5"				# Requested verbosity
LAN="$6"				# Language of the file to be OCRed
KEEP_TMP="$7"				# Keep the temporary files after processing (helpful for debugging)
PREPROCESS_DESKEW="$8"			# Deskew the page to be OCRed
PREPROCESS_CLEAN="$9"			# Clean the page to be OCRed
PREPROCESS_CLEANTOPDF="${10}"		# Put the cleaned paged in the OCRed PDF
OVERSAMPLING_DPI="${11}"		# Oversampling resolution in dpi
PDF_NOIMG="${12}"			# Request to generate also a PDF page containing only the OCRed text but no image (helpful for debugging) 
TESS_CFG_FILES="${13}"			# Specific configuration files to be used by Tesseract during OCRing
FORCE_OCR="${14}"			# Force to OCR, even if the page already contains fonts



################################## 
# Detect the characteristics of the embedded image for 
# the page number provided as parameter
#
# Param 1: page number
# Param 2: PDF page width in pt
# Param 3: PDF page height in pt
# Param 4: temporary file path (Path of the file in which the output should be written)
# Output:  A file containing the characteristics of the embedded image. File structure:
#          DPI=<dpi>
#          COLOR_SPACE=<colorspace>
#          DEPTH=<colordepth>
# Returns:
#       - 0: if no error occurs
#       - 1: in case the page already contains fonts (which should be the case for PDF generated from scanned pages) 
#       - 2: in case the page contains more than one image
##################################
getImgInfo() {
	local page widthPDF heightPDF curImgInfo nbImg curImg propCurImg widthCurImg heightCurImg colorspaceCurImg depthCurImg dpi

	# page number
	page="$1"
	# width / height of PDF page (in pt)
	widthPDF="$2"
	heightPDF="$3"
	# path of the file in which the output should be written
	curImgInfo="$4"

	
	[ $VERBOSITY -ge $LOG_DEBUG ] && echo "Page $page: Size ${heightPDF}x${widthPDF} (h*w in pt)"
	
	
	# check if the page already contains fonts (which should not be the case for PDF based on scanned files
	[ `pdffonts -f $page -l $page "${FILE_INPUT_PDF}" | wc -l` -gt 2 ] && echo "Page $page: Page already contains font data !!!" && return 1

	
	# extract raw image from pdf file to compute resolution
	# unfortunately this image can have another orientation than in the pdf...
	# so we will have to extract it again later using pdftoppm
	pdfimages -f $page -l $page -j "$FILE_INPUT_PDF" "$curOrigImg" 1>&2	
	# count number of extracted images 
	nbImg=$((`ls -1 "$curOrigImg"* 2>/dev/null | wc -l`))
	if [ $nbImg -ne "1" ]; then
		[ $VERBOSITY -ge $LOG_WARN ] && echo "Page $page: Expecting exactly 1 image covering the whole page (found $nbImg). Cannot compute dpi value."
		return 2
	fi
	# Get characteristics of the extracted image
	curImg=`ls -1 "$curOrigImg"* 2>/dev/null`
	propCurImg=`identify -format "%w %h %[colorspace] %[depth]" "$curImg"`
	widthCurImg=`echo "$propCurImg" | cut -f1 -d" "`
	heightCurImg=`echo "$propCurImg" | cut -f2 -d" "`
	colorspaceCurImg=`echo "$propCurImg" | cut -f3 -d" "`
	depthCurImg=`echo "$propCurImg" | cut -f4 -d" "`
	[ $VERBOSITY -ge $LOG_DEBUG ] && echo "Page $page: Size ${heightCurImg}x${widthCurImg} (in pixel)"	

	# compute the resolution of the image (making the assumption that x & y resolution are equal)
	# and round it to the nearest integer
	dpi=`echo "scale=5;sqrt($widthCurImg*72*$heightCurImg*72/$widthPDF/$heightPDF)+0.5" | bc`
	dpi=`echo "scale=0;$dpi/1" | bc`
	
	# save the image characteristics
	echo "DPI=$dpi" > "$curImgInfo"
	echo "COLOR_SPACE=$colorspaceCurImg" >> "$curImgInfo"
	echo "DEPTH=$depthCurImg" >> "$curImgInfo"
	
	return 0
}


page=`echo $PAGE_INFO | cut -f1 -d" "`
[ $VERBOSITY -ge $LOG_INFO ] && echo "Processing page $page / $NUM_PAGES"

# get width / height of PDF page (in pt)
widthPDF=`echo $PAGE_INFO | cut -f2 -d" "`
heightPDF=`echo $PAGE_INFO | cut -f3 -d" "`

# create the name of the required temporary files
curOrigImg="$TMP_FLD/${page}.orig-img"				# original image available in the current PDF page 
								# (the image file may have a different orientation than in the pdf file)
curHocr="$TMP_FLD/${page}.hocr"					# hocr file to be generated by the OCR SW for the current page
curOCRedPDF="$TMP_FLD/${page}.ocred.pdf"			# PDF file containing the image + the OCRed text for the current page
curOCRedPDFDebug="$TMP_FLD/${page}.ocred.todebug.pdf"		# PDF file containing data required to find out if OCR worked correctly
curImgInfo="$TMP_FLD/${page}.orig-img-info.txt"			# Detected characteristics of the embedded image


# auto-detect the characteristics of the embedded image
depthCurImg="8"			# default color depth
colorspaceCurImg="sRGB"		# default color space
dpi=$DEFAULT_DPI		# default resolution

getImgInfo "$page" "$widthPDF" "$heightPDF" "$curImgInfo"
ret_code="$?"

# in case the page contains text do not OCR, unless the FORCE_OCR flag is set
if ([ "$ret_code" -eq "1" ] && [ "$FORCE_OCR" -eq "0" ]); then
	echo "Page $page: Exiting... (Use the -f option to force OCRing, even though fonts are available in the input file)" && exit $EXIT_BAD_INPUT_FILE
elif ([ "$ret_code" -eq "1" ] && [ "$FORCE_OCR" -eq "1" ]); then
	[ $VERBOSITY -ge $LOG_WARN ] && echo "Page $page: OCRing anyway, assuming a default resolution of $dpi dpi"
# in case the page contains more than one image, warn the user but go on with default parameters
elif [ "$ret_code" -eq "2" ]; then
	[ $VERBOSITY -ge $LOG_WARN ] && echo "Page $page: Continuing anyway, assuming a default resolution of $dpi dpi"
else
	# read the image characteristics from the file
	dpi=`cat "$curImgInfo" | grep "^DPI=" | cut -f2 -d"="`
	colorspaceCurImg=`cat "$curImgInfo" | grep "^COLOR_SPACE=" | cut -f2 -d"="`
	depthCurImg=`cat "$curImgInfo" | grep "^DEPTH=" | cut -f2 -d"="`
fi

# perform oversampling if the resolution is not sufficient to get good OCR results
if [ "$dpi" -lt "$OVERSAMPLING_DPI" ]; then
	[ $VERBOSITY -ge $LOG_WARN ] && echo "Page $page: Low image resolution detected ($dpi dpi). Performing oversampling ($OVERSAMPLING_DPI dpi) to try to get better OCR results." 
	dpi="$OVERSAMPLING_DPI"
elif [ "$dpi" -lt "200" ]; then
	[ $VERBOSITY -ge $LOG_WARN ] && echo "Page $page: Low image resolution detected ($dpi dpi). If needed, please use the \"-o\" to try to get better OCR results." 
fi
	
# Identify if page image should be saved as ppm (color), pgm (gray) or pbm (b&w)
ext="ppm" 	# by default (color image) the extension of the extracted image is ppm
opt=""		# by default (color image) no option as to be passed to pdftoppm
if [ "$colorspaceCurImg" = "Gray" ] && [ "$depthCurImg" = "1" ]; then		# if monochrome (b&w)
	ext="pbm"
	opt="-mono"
elif [ "$colorspaceCurImg" = "Gray" ]; then					# if gray
	ext="pgm"
	opt="-gray"
fi
curImgPixmap="$TMP_FLD/$page.$ext"
curImgPixmapDeskewed="$TMP_FLD/$page.deskewed.$ext"
curImgPixmapClean="$TMP_FLD/$page.cleaned.$ext"

# extract current page as image with correct orientation and resolution
[ $VERBOSITY -ge $LOG_DEBUG ] && echo "Page $page: Extracting image as $ext file (${dpi} dpi)"
! pdftoppm -f $page -l $page -r $dpi $opt "$FILE_INPUT_PDF" > "$curImgPixmap" \
	&& echo "Could not extract page $page as $ext from \"$FILE_INPUT_PDF\". Exiting..." && exit $EXIT_OTHER_ERROR

# if requested deskew image (without changing its size in pixel)
widthCurImg=$(($dpi*$widthPDF/72))
heightCurImg=$(($dpi*$heightPDF/72))
if [ "$PREPROCESS_DESKEW" -eq "1" ]; then
	[ $VERBOSITY -ge $LOG_DEBUG ] && echo "Page $page: Deskewing image"
	! convert "$curImgPixmap" -deskew 40% -gravity center -extent ${widthCurImg}x${heightCurImg} "$curImgPixmapDeskewed" \
		&& echo "Could not deskew \"$curImgPixmap\". Exiting..." && exit $EXIT_OTHER_ERROR
else
	ln -s `basename "$curImgPixmap"` "$curImgPixmapDeskewed"
fi

# if requested clean image with unpaper to get better OCR results
if [ "$PREPROCESS_CLEAN" -eq "1" ]; then
	[ $VERBOSITY -ge $LOG_DEBUG ] && echo "Page $page: Cleaning image with unpaper"
	! unpaper --dpi $dpi --mask-scan-size 100 \
		--no-deskew --no-grayfilter --no-blackfilter --no-mask-center --no-border-align \
		"$curImgPixmapDeskewed" "$curImgPixmapClean" 1> /dev/null \
		&& echo "Could not clean \"$curImgPixmapDeskewed\". Exiting..." && exit $EXIT_OTHER_ERROR
else
	ln -s `basename "$curImgPixmapDeskewed"` "$curImgPixmapClean"
fi

# perform OCR
[ $VERBOSITY -ge $LOG_DEBUG ] && echo "Page $page: Performing OCR"
! tesseract -l "$LAN" "$curImgPixmapClean" "$curHocr" hocr $TESS_CFG_FILES 1> /dev/null 2> /dev/null \
	&& echo "Could not OCR file \"$curImgPixmapClean\". Exiting..." && exit $EXIT_OTHER_ERROR
mv "$curHocr.html" "$curHocr"

# embed text and image to new pdf file
if [ "$PREPROCESS_CLEANTOPDF" -eq "1" ]; then
	image4finalPDF="$curImgPixmapClean"
else
	image4finalPDF="$curImgPixmapDeskewed"	
fi
[ $VERBOSITY -ge $LOG_DEBUG ] && echo "Page $page: Embedding text in PDF"
! python2 $SRC/hocrTransform.py -r $dpi -i "$image4finalPDF" "$curHocr" "$curOCRedPDF" \
	&& echo "Could not create PDF file from \"$curHocr\". Exiting..." && exit $EXIT_OTHER_ERROR

# if requested generate special debug PDF page with visible OCR text
if [ $PDF_NOIMG -eq "1" ] ; then
	[ $VERBOSITY -ge $LOG_DEBUG ] && echo "Page $page: Embedding text in PDF (debug page)"
	! python2 $SRC/hocrTransform.py -b -r $dpi "$curHocr" "$curOCRedPDFDebug" \
		&& echo "Could not create PDF file from \"$curHocr\". Exiting..." && exit $EXIT_OTHER_ERROR	
fi

# delete temporary files created for the current page
# to avoid using to much disk space in case of PDF files having many pages
if [ $KEEP_TMP -eq 0 ]; then
	rm -f "$curOrigImg"*
	rm -f "$curHocr"
	rm -f "$curImgPixmap"
	rm -f "$curImgPixmapDeskewed"
	rm -f "$curImgPixmapClean"
	rm -f "$curImgInfo"
fi

exit 0