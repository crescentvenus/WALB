### Copy webui/* to youre WWW server. (ie. /var/wwww/html/webui )
### Change all of the file owner to match HTTP server's process owner. (ie. www-data:www-data)

### Clicked location on the map will be written to file LatLon.txt by LatLon.php. (i.e.  35.88258,139.49264,100,14,400)
### Script smooth2.php will interpolate from previous location to just clicked location and write it to "/tmp/LatLon.txt" every 1/10 second.
### Enhanced gps-sdr-sim reads the file "/tmp/LatLon.txt" and generate I/Q signal.
