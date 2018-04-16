#!/bin/bash

APP_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../../../" && pwd )"
BACKUP_DIR="$APP_DIR/storage/db"
# BACKUP_DIR=$(DIR)
DATE=`date +%Y%m%d`
BACKUP_FILE="${DATE}.tar.gz"
BACKUP_FILE_ENCRYPTED="${DATE}.tar.gz.enc"
BACKUP_PASSPHRASE_FILE="${DATE}.passphrase"
BACKUP_PASSPHRASE_FILE_ENCRYPTED="${DATE}.passphrase.enc"

export $(egrep -v '^#' .env | xargs)

# echo ${APP_ENV}
# exit


# openssl genrsa -out key.pem 2048
# openssl rsa -in private-key.pem -out pub.pem -outform PEM -pubout

# Make the directory just in case it doesn't exist
# mkdir ${BACKUP_DIR}
cd ${BACKUP_DIR}

# ls -lah
# exit

# Delete the oldest files by only listing out everything older than the newest 7 files
# ls *.gz.enc | sort | tail -n +7 | xargs rm
# ls *.gz.passphrase.enc | sort | tail -n +7 | xargs rm

# Backup the MySQL databases
mysqldump -u${DB_USERNAME} -p${DB_PASSWORD} -h${DB_HOST} ${DB_DATABASE} > ${DB_DATABASE}.sql

# Tar GZ everything (modify this line to include more files and directories in the backup)
tar -pczf ${BACKUP_FILE} *.sql

# Generate a random passphrase
openssl rand 32 -out ${BACKUP_PASSPHRASE_FILE}

# Encrypt the backup tar.gz
openssl enc -aes-256-cbc -pass file:${BACKUP_PASSPHRASE_FILE} < ${BACKUP_FILE} > ${BACKUP_FILE_ENCRYPTED}

# Encrypt the passphrase
openssl rsautl -encrypt -pubin -inkey ./pub.pem < ${BACKUP_PASSPHRASE_FILE} > ${BACKUP_PASSPHRASE_FILE_ENCRYPTED}

# Clean up
rm ${BACKUP_FILE} ${BACKUP_PASSPHRASE_FILE}

# Copy offsite
#scp ${BACKUP_FILE_ENCRYPTED} ${BACKUP_PASSPHRASE_FILE_ENCRYPTED} <username>@<backup-server>:backups/.

# Decrypt
# If you ever need to use a backup, you will need to decrypt it first. To decrypt a backup, 
# you will need the private key you created above and the two encrypted files that the 
# backup script created (the encrypted tar.gz and the encrypted passphrase file). 
# If you are in a directory that contains all of these files, execute these 
# commands to decrypt the backup (you'll need to change the file names below to match your file names):

# openssl rsautl -decrypt -inkey private-key.pem < 20160502.passphrase.enc > 20160502.passphrase
# openssl enc -aes-256-cbc -d -pass file:20160502.passphrase < 20160502.tar.gz.enc > 20160502.tar.gz