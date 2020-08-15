# phpbb-ext-geomoderate

[![Build Status](https://travis-ci.org/gothick/phpbb-ext-geomoderate.svg?branch=master)](https://travis-ci.org/gothick/phpbb-ext-geomoderate)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gothick/phpbb-ext-geomoderate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gothick/phpbb-ext-geomoderate/?branch=master)

**Note**: This is not official phpBB software. Recent releases should be compatible with
phpBB 3.3.x (tested with 3.3.1). It seems to work for me, but your mileage may vary, and
I give no guarantees!

A phpBB Extension that moderates all posts from certain countries, based on the Maxmind 
GeoLite2 Country database.

* Install in the normal way.
* Configure under Extensions->GeoModerate Settings. Tick the box for any country of origin
whose posts you'd like to go to the moderation queue.
* Profit.

Admins and moderators will bypass the check automatically. Any moderation action taken by 
the Extension will appear in the phpBB Moderation log. If things don't seem to be working,
check the phpBB Error log; if anything goes wrong this extension *should* fail gracefully
and quietly, logging an error and letting posts through.

This product includes GeoLite2 data created by MaxMind, available from 
[http://www.maxmind.com](http://www.maxmind.com).
