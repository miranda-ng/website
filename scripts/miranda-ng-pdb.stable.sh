##!/bin/sh
#define path variables
tempdir=/tmp/miranda-stable
wwwdir=/www/miranda-ng.org/htdocs/distr/stable
#sync with official repository
#rsync --recursive --delete basil@miranda-ng.org:/var/www/miranda-ng.org/htdocs/distr/ $wwwdir/

#calculate md5 hashes of current files and our previous files
#md5x32_new=`md5sum $wwwdir/x32/hashes.zip | cut -f 1 -d ' '`
#md5x64_new=`md5sum $wwwdir/x64/hashes.zip | cut -f 1 -d ' '`
#md5x32_cur=`md5sum $tempdir/hashes_x32.zip | cut -f 1 -d ' '`
#md5x64_cur=`md5sum $tempdir/hashes_x64.zip | cut -f 1 -d ' '`

#recalculate a crc32 of our repacked files to update hases in hashes.txt
#correct hash will fix "fail" in PluginUpdater update process
function crc_calc() {
        unzip -o $wwwdir/$1/hashes.zip -d $tempdir/
        while read -r file hash crc_old
        do
        #first, change widows backslash to linux slash
        name=$(sed -e 's/\\/\//' <<< "$file")
        #replace txt, dll or exe extension to zip
        name=$(sed -E 's/txt|dll|exe/zip/' <<< "$name")
        #change  string to lowercase, but it's also changes foldername (Plugins, Icons) into lowercase, thus a simlink required to locate file.
        #ln -s Plugins plugins; ln -s Icons icons
        name=$(tr [:upper:] [:lower:] <<< "$name")
        #now we got a foldername and filename, lets check crc32 for new archive
        #echo processing  $wwwdir/pdb_$1/$name
        crc=$(/usr/local/bin/crc32 $wwwdir/pdb_$1/$name)
        #output a file, hash from original hashses.txt with new crc32 into new hashes.txt
        echo $file $hash $crc >> $wwwdir/pdb_$1/hashes.txt
        done < $tempdir/hashes.txt
        #finish all lines processing, zip new hashes
        zip -m  --junk-paths $wwwdir/pdb_$1/hashes.zip $wwwdir/pdb_$1/hashes.txt
        rm $tempdir/hashes.txt
        logger -t Miranda $1 pdb repo updated
}
#repack arhives with adding a pdb file for each plugin and core
function repack() {
        #add suff variable if we are processing x64, this is for  pdb archive name.
        if [ "$1" == "x64" ]
        then suff=_x64
        fi
        #copy hases.txt into tempdir, we will check this files md5 in next script run from cron
        cp -f $wwwdir/$1/hashes.zip $tempdir/hashes_$1.zip
        #purge folder before copying plugins
        rm -rf $wwwdir/pdb_$1/Plugins/*
        rm -rf $wwwdir/pdb_$1/Icons/*
        rm -rf $wwwdir/pdb_$1/Languages/*
        #copy archives into pdb dir
        cp -rf $wwwdir/$1/* $wwwdir/pdb_$1/
        #unpack big pdb archive into temdir
        7za x -aoa $wwwdir/miranda-ng-debug-symbols_pdb${suff}.7z -o$tempdir/pdb_$1/
        #go to pdb dir
        cd $tempdir/pdb_$1/
        #for each zip file with core or plugin, list a zip files and find pdb files with same name. Once find succeed, add this pdb into root of archive
        for a in `find  $wwwdir/pdb_$1/ -iname *.zip`; do zip $a -g `for b in \`unzip -qqql $a | tr -s ' '| grep -e dll -e mir -e exe | cut -f 5 -d' ' | cut -f 2,3 -d/ | cut -f 2 -d/ | cut -f 1 -d.\`; do echo $b.pdb;done;`;done;
        #call a crc calc function
        crc_calc $1
        }

#Check md5 sum of current hashes and stored from previous run
#if [ "${md5x32_new}" != "${md5x32_cur}" ]
#if md5 differ, thus files was updated with new build, run repack function
#then
#repack x32
#else
#echo no x32 update
#fi

#same check for x64 hashes.
#if [ "${md5x64_new}" != "${md5x64_cur}" ]
#then
repack x32
repack x64
#else
#echo no miranda pdb update
#fi
