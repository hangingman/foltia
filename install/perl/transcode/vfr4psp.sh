#! /bin/sh
# This script based http://www.kokone.to/~kgt/diary.shtml?200501#23_3

# vi:set ts=4 sw=4:

umask 7077

START_OPT=""
DURATION_OPT=""


cd $3

NUMBER=$2
#if [ "$NUMBER" -eq "" ]
#then
#
#NUMBER_FILE=$HOME/.psp_movie_number
#NUMBER=`(cat $NUMBER_FILE | grep '^[0-9]*$' | head -1) 2> /dev/null`
#if [ "$NUMBER" -eq "" ]
#then
#	NUMBER=1
#else
#	NUMBER=`expr $NUMBER + 1`
#fi
#echo $NUMBER > $NUMBER_FILE
#fi

# OUTPUT=`printf "M4V%05d.MP4" $NUMBER`
OUTPUT="M4V$NUMBER.MP4"

PREFIX=`mktemp -d 4psp-XXXXXXX`
TMP_M4V="$PREFIX/temporary.m4v"
TMP_LOG="$PREFIX/temporary"
TMP_S16="$PREFIX/temporary.s16"
TMP_PCM="$PREFIX/temporary.s16"
#TMP_PCM="$PREFIX/temporary.pcm"
TMP_AAC="$PREFIX/temporary.aac"

#
# Encode
#

rm -f $TMP_M4V $TMP_S16

ASPECT=$4
if [ "$ASPECT" -eq "" ]
then
ASPECT="3";
fi

if [ "$ASPECT" -eq "16" ]
then
#16:9 
/usr/local/bin/ffmpeg -y -i $1 -vcodec xvid -croptop 70 -cropbottom 60 -cropleft  8 -cropright 14 -s 320x240 -b 300 -bt 128 -r 14.985  -hq -nr -qns -bufsize 192 -maxrate 512 -minrate 0  -deinterlace  -acodec pcm_s16le -ar 24000 -ac 2 -f m4v $TMP_M4V -f s16le $TMP_S16

else
#3:4
/usr/local/bin/ffmpeg -y -i $1 -vcodec xvid -croptop 8 -cropbottom 8 -cropleft  8 -cropright 14 -s 320x240 -b 300 -bt 128 -r 14.985  -hq -nr -qns -bufsize 192 -maxrate 512 -minrate 0 -deinterlace  -acodec pcm_s16le -ar 24000 -ac 2 -f m4v $TMP_M4V -f s16le $TMP_S16

fi

# delay audio, 300->125msec (x24)
#dd if=/dev/zero bs=4 count=7200 of=$TMP_PCM 2> /dev/null
#dd if=/dev/zero bs=4 count=3000 of=$TMP_PCM 2> /dev/null
#cat $TMP_S16 >> $TMP_PCM


# AAC encode
/usr/local/bin/faac -o $TMP_AAC -q 100 -b 96 -R 24000 -B 16 -C 2 -X --mpeg-vers 4 --obj-type LC $TMP_PCM


#
# Build MP4 system file
#
TIMESTUMP=`date "+%Y%m%d-%H%M%S"`

if [ -s $OUTPUT ]
then
#echo EXIST
#mv $OUTPUT $OUTPUT.$TIMESTUMP.MP4
OUTPUT=$OUTPUT.$TIMESTUMP.MP4
fi

# Add video track
/usr/local/bin/mp4creator  -r 14.985 --create=$TMP_M4V $OUTPUT

# Add audio track
/usr/local/bin/mp4creator -aac-profile=4 $TMP_AAC $OUTPUT

# Delete invaid track
/usr/local/bin/mp4creator -delete=5 $OUTPUT
/usr/local/bin/mp4creator -delete=6 $OUTPUT

#
# Post process
#
rm -fr $PREFIX
