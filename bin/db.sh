#!/bin/bash

RED='\033[0;31m'
NC='\033[0m' # No Color

#APP_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../../../" && pwd )"
#APP_DIR="$(dirname "$0")/../"
APP_DIR="$(pwd)/"
BACKUP_DIR="$APP_DIR/storage/db"
# BACKUP_DIR=$(DIR)
DATE_FORMAT="%Y%m%d"
#if [ -z $3 ] && [ $3 -ne "" ]; then
	echo "setting date format to $3"
	DATE_FORMAT=$3
#fi

DATE=`date "+${DATE_FORMAT}"`
BACKUP_FILE="${DATE}.tar.gz"
BACKUP_FILE_ENCRYPTED="${DATE}.tar.gz.enc"
BACKUP_PASSPHRASE_FILE="${DATE}.passphrase"
BACKUP_PASSPHRASE_FILE_ENCRYPTED="${DATE}.passphrase.enc"
PUBLIC_PEM_FILE="${APP_DIR}storage/misc/pub.pem"
OPENSSL_IS_INSTALLED="$(openssl version)"
MYSQLDUMP_IS_INSTALLED="$(mysqldump --version)"
COMPRESS=$1
ENCRYPT=$2

#echo $DATE
#echo "DATEFORMAT: $DATE_FORMAT"
#exit

#echo $APP_DIR
#cd $APP_DIR && ls -la

if [ -z "$OPENSSL_IS_INSTALLED" ]; then
	echo "## OpenSSL is required."
	echo
	echo "Debian/Ubuntu: sudo apt-get install openssl"
	echo "RedHat/CentOS: yum install openssl"
	exit
fi

if [ -z "$MYSQLDUMP_IS_INSTALLED" ]; then
	echo "## mysqldump is required."
	exit
fi

function fileExists {
    if [ ! -f "$1" ]; then
		echo -e "File not found: ${RED}$1${NC}"
		exit
	fi
}

function e {
    echo -e "## ${RED} $1 ${NC}";
}

fileExists "$PUBLIC_PEM_FILE"
fileExists "$APP_DIR.env"

export $(egrep -v '^#' .env | xargs)

# Check if db credentials are all set. Maybe set the default DB_HOST to 127.0.0.1 (localhost)
if [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ] || [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ]; then
	echo "Database credentials are missing"
	exit
fi

# openssl genrsa -out key.pem 2048
# openssl rsa -in private-key.pem -out pub.pem -outform PEM -pubout

# Make the directory just in case it doesn't exist
# mkdir ${BACKUP_DIR}
cd ${BACKUP_DIR}

#echo $(pwd)

#echo "Encrypt $2"
#echo "Compress $1"

#exit

# ls -lah
# exit

# Delete the oldest files by only listing out everything older than the newest 7 files
# ls *.gz.enc | sort | tail -n +7 | xargs rm
# ls *.gz.passphrase.enc | sort | tail -n +7 | xargs rm

e "Backup the MySQL databases"
mysqldump -u${DB_USERNAME} -p${DB_PASSWORD} -h${DB_HOST} ${DB_DATABASE} > ${DB_DATABASE}.sql

# Tar GZ everything (modify this line to include more files and directories in the backup)
if [ $COMPRESS -eq "compress" ]; then
	e "Tar GZ everything"
	tar -pczf ${BACKUP_FILE} *.sql
fi

if [ $ENCRYPT -eq "encrypt" ]; then
	e "Generate a random passphrase"
	openssl rand 32 -out ${BACKUP_PASSPHRASE_FILE}

	e "Encrypt the backup tar.gz"
	openssl enc -aes-256-cbc -pass file:${BACKUP_PASSPHRASE_FILE} < ${BACKUP_FILE} > ${BACKUP_FILE_ENCRYPTED}

	e "Encrypt the passphrase"
	openssl rsautl -encrypt -pubin -inkey ${PUBLIC_PEM_FILE} < ${BACKUP_PASSPHRASE_FILE} > ${BACKUP_PASSPHRASE_FILE_ENCRYPTED}

	e "Clean up"
	rm ${BACKUP_FILE} ${BACKUP_PASSPHRASE_FILE}
fi

#e "Copy offsite (Where to?)"
#scp ${BACKUP_FILE_ENCRYPTED} ${BACKUP_PASSPHRASE_FILE_ENCRYPTED} <username>@<backup-server>:backups/.

# Decrypt
# If you ever need to use a backup, you will need to decrypt it first. To decrypt a backup, 
# you will need the private key you created above and the two encrypted files that the 
# backup script created (the encrypted tar.gz and the encrypted passphrase file). 
# If you are in a directory that contains all of these files, execute these 
# commands to decrypt the backup (you'll need to change the file names below to match your file names):

# openssl rsautl -decrypt -inkey private-key.pem < 20160502.passphrase.enc > 20160502.passphrase
# openssl enc -aes-256-cbc -d -pass file:20160502.passphrase < 20160502.tar.gz.enc > 20160502.tar.gz