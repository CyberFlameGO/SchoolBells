# SchoolBells
I created this as we had an old bell system that worked great, but in order to change the times we had to remote into a server and because of where we had that running it was difficult to give multiple people access to it.

This uses the same usb relay as that system:<br>
<img src="https://github.com/mathsnz/SchoolBells/raw/main/relay.jpg" height=200>

But has a web interface for scheduling the bells.<br>
<img src="https://github.com/mathsnz/SchoolBells/raw/main/screenshot.png" height=300>
<img src="https://github.com/mathsnz/SchoolBells/raw/main/screenshot2.png" height=300>

Released as is where is for you to take as needed.

To run you need a usb relay, and a web server running PHP (can just be a crappy old machine)<br>
You'll also need to add ring.php to your cron job list to run every minute like this:<br>
`* * * * * cd /var/www/html && php ring.php`<br>
You will also want to go and change the username and password in index.php<br>
It's not the most amazing system in the world, and could probably be written better... but for an hour's work it works.

**Our current setup:**
- Web server running on raspberry pi (although could run on any old - or new - computer you have lying around)
- Raspberry Pi has a static IP
- USB relay plugged into that
