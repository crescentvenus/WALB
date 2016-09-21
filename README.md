# WALB ( Wireless Attack Launch Box ) 
## What is WALB ?
* WALB is a Raspberry Pi2/Pi3 and HackRF based lunch box sized portable RF signal generator.
* The intended purpose of the WALB development is to test or demonstrate the security issue of wireless devices and location based applications.
* By preparing a I/Q binary data, it is possible to generate any signal in the frequency range available to HackRF.
* For GPS and ADS-B, real time signal generator module is included in WALB.
* It uses HackRF as a SDR unit with enhanced GPS-SDR-SIM for GPS signal generation.
* It has a 8x2 LCD and a rotary encoder with two color LED and a push switch for the operation of WALB.
* Since WALB works with battery powered, you can use it any where you like.
* Adding new simulation scenario or signal generation, it can be achieved by SSH login and simply edit the menu items using your favorite text editor. 
* By preparing the binary I / Q signal file of 8 bit signed, you can generate arbitrary RF signals.
* To do so, you simply need to edit and add TEXT menu items specifying the filename of I/Q file, frequency, and sample rate.
* If you prepare an external program to generate the I / Q signal in real time,you can also add the program and/or script in the menu. 
* You can set or chose GPS spoofing scenario by predefined location and/or date & time.

![PICT](https://github.com/crecentmoon/WALB/blob/master/images/WALB.png)

## Prerequisites. 
You need to install GPS-SDR-SIM,HackRF host tools, and WireringPi on RaspberryPi.<br>
Rapsberry Pi3 is highly recomended for better performance of the real time signal generation.<br>
The installation instruction links are as follows.<br> 
RaspberryPi:
https://www.raspberrypi.org/<br>
GPS-SDR-SIM: 
https://github.com/osqzss/gps-sdr-sim<br>
HackRF:
https://github.com/mossmann/hackrf<br>
WireringPi:
http://wiringpi.com/download-and-install/
<br><br>
## directory structure of WALB software:<br>
```
/home/pi/
        /IQ-files    ... binary I/Q files to pass hackRF or text files used for genaration of I/Q file by 
                         dedicated real time signal generation program such as enhanced GPS-SDR-SIM, or ADS-B_gen
        /gps-sdr-sim ... GPS-SDR-SIM files.
        replay2      ... Main startup program of the WALB
        menu2.txt    ... Main menu items displayed on LCD
        level2.txt   ... Sub menu-1: transmit power setteing
        date2.txt    ... Sub menu-2: date&time setting for GPS time spoofing
        scripts/
                sim_start.sh ... Script to start I/Q signal generation and kick HackRF to transmit
                ic2-disp.sh  ... Script to control LCD
                stat.sh      ... Script to check if hackrf_transfer is active
                kill_proc.sh ... Script to kill gps-sdr-sim and/or hackrf_transfer
                eth.sh       ... Script to display eth0 IP address on LCD
                wlan.sh      ... Script to display wlan0 IP address on LCD
		python/		... Python port files from replay2.c

/var/www/html/pokePos/    ... files to set GPS-SDR-SIM location via Web UI. 
```    
## Installation
# compile
gcc replay2.c -I/usr/local/include -L/usr/local/lib -lwiringPi -o replay2<br>
(You may need to adjust -I/Lxxxxx for the location of wireringPi)<br>

## Usage demo links.<br>
* Wireless Attack Launch Box operation demo. (Length: 3 minutes)<br>
https://www.youtube.com/watch?v=SIPCqLmJFig<br>
<br>
* Real time GPS signal generation by WALB. (Length: 2 minutes)<br>
https://www.youtube.com/watch?v=-V4KLIqEzQg<br>
<br>
* GPS time spoofing demo intended to test GPS week number rollover issue. (Length: 140 seconds)<br>
https://www.youtube.com/watch?v=mEU5RjRJ2lI<br>
<br>
* ADS-B replay attack demo.( Length: 2 minites)<br>
https://www.youtube.com/watch?v=APc1hreOkYU<br>
