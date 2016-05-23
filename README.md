# WALB ( Wireless Attack Launch Box ) 
## What is WALB ?
* WALB is a Raspberry Pi2/Pi3 based portable GPS signal generator.
* It uses HackRF as a SDR unit with enhanced GPS-SDR-SIM.
* It has a 8x2 LCD and rotally encorder with two color LED for opration of WALB.
* You can set or chose GPS spoofing senario by predefeined location and/or date&time.
* Since WALB works with battery powered, you can use it any where you like.

## 特徴
* 8bit signedのバイナリーI/Q信号ファイルを準備すれば、メニュー項目（テキスト）の編集・追加で周波数、サンプルレートを任意に設定した信号の発生が可能
* リアルタイムでI/Q信号を発生する外部プログラムを準備すれば、メニュー項目と実行スクリプトの編集・追加で任意の信号の発生が可能
* GPSとADS-B信号については、リアルタイムで信号を生成するモジュールを内蔵

![PICT](http://git.lab.local/adviser/Wireless_Attack_Launch_Box/raw/master/WALB.png)

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
        /IQ-files    ... files to spoof GPS location;NMAE, CSV, or Fix Lat/Lon 
        /gps-sdr-sim ... GPS-SDR-SIM files.
        replay2      ... Main startup program of the WALB
        menu2.txt    ... Main menu items displayed on LCD
        level2.txt   ... Sub menu-1: transmit power setteing
        date2.txt    ... Sub menu-2: date&time setting for GPS time spoofing
        sim_start.sh ... Script to start I/Q signal generation and kick HackRF to transmit
        ic2-disp.sh  ...
        stat.sh      ...
        kill_proc.sh
        eth.sh
        wlan.sh
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
* GSP spoofing detection by android Tablet. (Length: 90 seconds)<br>
https://youtu.be/Hfqm7aartGw<br>
