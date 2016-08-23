This binary file is enhanced version of GPS-SDR-SIM, which is compiled for raspberry Pi 2/3 and HackRF as a SDR unit.

Options: * denotes enhanced feature.
  -e <gps_nav>     RINEX navigation file for GPS ephemerides (required)
  -u <user_motion> User motion file (dynamic mode)
  -g <nmea_gga>    NMEA GGA stream (dynamic mode)
  -l <location>    Lat,Lon,Hgt (static mode) e.g. 30.286502,120.032669,100
* -i <interactive motion> Interactive input from a file Lat,Lon,Hgt
  -t <date,time>   Scenario start time YYYY/MM/DD,hh:mm:ss
* -T <date,time>   Scenario start time YYYY/MM/DD,hh:mm:ss(No ristriction)
  -d <duration>    Duration [sec] (max: 3000)
  -o <output>      I/Q sampling data file (default: gpssim.bin)
  -s <frequency>   Sampling frequency [Hz] (default: 2600000)
  -b <iq_bits>     I/Q data format [1/8/16] (default: 16)
* -n <channles>    Number of channels [1..16] (default: 16)
  -v               Show details about simulated channels

Usage example for interactive mode:
 -----------------------------------
STEP 1) run script to start enhanced gps-sdr-sim 
    #!/bin/sh
    SAMPE=2048000
    POWER=0
    BRDC=brdc3640.15n
    N_SAT=16
    FIFO=/tmp/fifo
    INT_FILE=/tmp/latlon.txt
    DATE=2016/08/23,00:18:22
    mkfifo $FIFO
    ./gps-sdr-sim -s $SAMPLE -e $BRDC -i $INT_FILE -b8 -n $N_SAT -o $FIFO -T $DATE&
    hackrf_transfer -t $FIFO -f $FREQ -s $SAMPLE  -x $POWER >/dev/nul

STEP 2) Update the content of file INT_FILE every 1/10 second as necessary.

Notes:
    I have reduced the sample rate a bit from default(2600000) due to performance limitation of the raspberry Pi.
    You need additional user interface scripts to update INT_FILE as necessary.
    Use Google maps API for example.
