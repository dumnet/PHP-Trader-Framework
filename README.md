# IKYTraderFramework
After my first project Ichimoku Trader Framework, I'm working on some simple useful tools for market analysts and traders.

getrates.php : Get GER30/DAX/DAX30 indice values and store them in a local database, in a file, and prints them on screen.

The first script is relatively easy and get the rates of the GER30 (DAX30 / DAX) indice from FXCM XML provider and prints the rates on the screen and save the rates in a MYSQL database. If you only want to print the values on screen without storing them then you can comment the code specific to the database access (I'll add an option for enabling and disabling the database accesses).

Features : 
- Time between two rates is 1 second.
- GER30 (DAX30/DAX) rates in this first version.
- Save data into a local file named "GER30.TXT".
- Save data in a MySql database.
- FXCM Provider (Thanks FXCM !).

getrates*.php : Connect to FXCM every 1 second and gets the rates for indice GER30 or pair EURUSD and saves data in the mysql database, and prints data to screen also.

realtimeavg*.php and realtimeavg*eurusd.php : Calculates the average value of all the recorded data for the current day, and compares it to the last known rate. Prints --- or +++ to the screen (relative trend). Works with GER30 and EURUSD for now.

