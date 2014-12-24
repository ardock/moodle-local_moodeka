Moodeka web service for Moodle 2.X
------------------------------------------

The webservice plugin for the Moodeka app (https://github.com/mouneyrac/moodeka).

what does the webservice do
---------------------------
It returns the first 8 multi-choice questions of each courses where the user is enrolled. The web service function only look for questions into the default question category of each course.

SECURITY WARNING
----------------
There are no permissions check done on the questions (TODO). If some of your course questions are restricted to few participatans only, then you shoult NOT USE this plugin because been a course participant is enough to see all the course questions (however they should not see questions in course where they are not participants).

The code is offered as is, USE IT AT YOUR OWN RISK.

Installation
------------
1- install the plugin in moodle/local/moodeka/
2- enable mobile webservice
3- give the permission to createtoken to all authenticated user.
