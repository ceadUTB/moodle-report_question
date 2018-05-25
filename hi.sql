SELECT
   DISTINCT(qas.userid), qas.state,q.name
FROM mdl_quiz_attempts quiza
JOIN mdl_question_usages qu ON qu.id = quiza.uniqueid
JOIN mdl_question_attempts qa ON qa.questionusageid = qu.id
JOIN mdl_question_attempt_steps qas ON qas.questionattemptid = qa.id
JOIN mdl_question q ON q.id = qa.questionid
LEFT JOIN mdl_question_attempt_step_data qasd ON qasd.attemptstepid = qas.id
 
WHERE quiza.quiz = 8 AND qas.state IN ("gradedwrong", "gradedright");