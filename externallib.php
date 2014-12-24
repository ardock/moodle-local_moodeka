<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_moodeka_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_quiz_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_quiz() {
        global $USER, $DB, $CFG;


        $randomcolors = array('green', 'yellow', 'blue', 'red', 'purple');
        $randomicons = array('entertainment', 'sports', 'music', 'tvmovies', 'science', 'geography', 'history', 'knowledge', 'food');
        $randomsingletypes = array('single-select-item', 'single-select');

        //Parameter validation
        $params = self::validate_parameters(self::get_quiz_parameters(), array());

        //Context validation
        //self::validate_context(context_user::get_instance($USER->id));

        //Capability checking
        //if (!has_capability('moodle/user:viewdetails', $context)) {
        //   throw new moodle_exception('cannotviewprofile');
        //}

        $courses = enrol_get_users_courses($USER->id, true, 'id, shortname, fullname, idnumber, visible');

        $result = array();

        foreach ($courses as $course) {

            // Find all questions the user is allowed to see
            $coursecontext = context_course::instance($course->id);
            $questions = array();
            require_once($CFG->dirroot . "/question/editlib.php");
            
            $category = question_get_default_category($coursecontext->id);
            $catquestions = get_questions_category($category);

            // only keep multichoice yet.
            foreach ($catquestions as $question) {
                if ($question->qtype == "multichoice") {
                    $questions[$question->id] = $question;
                }
            }

            if (!empty($questions)) {

                // pick random theme color and icon.
                $color = $randomcolors[array_rand($randomcolors)];
                $icon = $randomicons[array_rand($randomicons)];
                $quizresult = new stdClass();
                $quizresult->name = $course->shortname;
                $quizresult->id = $icon;
                $quizresult->theme = $color;
                $quizresult->quizzes = array();
                // Only get 8 questions maxim
                $i = 0;
                foreach($questions as $question) {
                   if ($i<8) {

                     $i++;

                      // Convert them into JSON expected code
                      $questionresult = new stdClass();
                      $questionresult->question = clean_param($question->questiontext, PARAM_TEXT);
                      $questionresult->options = array();
                      $answernum = 0;
                      $jsonanswer = array(); 
                      foreach ($question->options->answers as $answer) {
                        // Find out if the answer is the correct one (obviouly we don't support multiple answer yet)
                        if (($answer->fraction) > 0) {
                           $jsonanswer[] = $answernum;
                        }
                        $questionresult->options[] = clean_param($answer->answer, PARAM_TEXT);
                        $answernum++;
                      }    

                      // Detect moodeka quiz type. 
                     if (count($jsonanswer) > 1) {
                          $type = 'multi-select';
                     } else {
                         if ($answernum == 4 and rand(0,1)) {
                             $type = 'four-quarter';
                          } else {
                             $type = $randomsingletypes[array_rand($randomsingletypes)];
                          }
                     }
                   
                      $questionresult->answer = $jsonanswer;
                      $questionresult->type = $type;

                      $quizresult->quizzes[] = $questionresult;
                    }
                }

               $result[] = $quizresult;
            }

        }
        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_quiz_returns() {
        return new external_multiple_structure(
            new external_single_structure( array (
                'name' => new external_value(PARAM_TEXT, 'Quiz name on the moodeka main page'),
                'id' => new external_value(PARAM_TEXT, 'Quiz shortname - likely to be a moodeka icon'),
                'theme' => new external_value(PARAM_TEXT, 'color of the quiz'),
                'quizzes' => new external_multiple_structure(
                                new external_single_structure( array (
                                     'type' => new external_value(PARAM_TEXT, 'question type'),
                                     'question' => new external_value(PARAM_TEXT, 'question'),
                                     'options' => new external_multiple_structure(new external_value(PARAM_TEXT, 'question choice')),
                                     'answer' => new external_multiple_structure(new external_value(PARAM_TEXT, 'possible answer')),

                )))
            ))
        );        
    }



}
