#!/bin/bash
####
# You can output the contents of this script to a file via cronjob and use a standard web monitoring service check the string. 
# If there is less than 20% free disk space the usage will be output breaking the check
# example crontab entry: 
# */5 * * * * /bin/bash /home/ec2-user/bin/disk-space.sh > /var/www/html/space.html
####
o="$HOSTNAME"
o+="$(/bin/df -P /dev/xvda1 | /usr/bin/awk '0+$5 >= 80 {print}')"
o+='-end'
echo $o