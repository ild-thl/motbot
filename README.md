This is a moodle plugin under development. It allows moodle managers to add motbot activitys to their courses. These activitys add a Motivational-Bot (MotBot) to the course, that will use learning analytics data to identify students in need and intervene if necessary.
These interventions include personalized advice to help the students to improve their learning behaviour. Students are able to customize and deactivate all MotBot features, to support their self-sovereignty. Teachers can create custom templates for the diffrent interventions and activate analytics models for their courses as they see fit.

At this point following usecases are fully supported:
- No recent accesses
- Low social activity


## Installation
1. Install Moodle 4.0 or later.
2. Clone this repo to /<your_moodle_installation>/mod/motbot.
3. As an admin follow the upgrade steps prompted to you by moodle.
4. Go to site administration -> analytics -> analytics models and enable all motbot models and set appropriate time intervals.
5. Add a motbot activity to a course and set active. (Enroled students have to enable their motbot individually before the bot cal analyse their larning activity)

## Compatible messaging plugins
This plugin supports sending messages using Telegram and Signal. The following plugins have to be installed to enable these features.

### Telegram plugin
    git clone https://github.com/pascalhuerten/moodle_telegram.git
    
### Signal plugin
    git clone https://github.com/pascalhuerten/moodle_signal.git
