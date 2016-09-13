The PHP files directly placed into this folder are "parent project" files.
Example, "learnapp.php" is the parent file that will be extended by all the PHP files placed into the "learnapp" folder.
So, consider that all the PHP files at root of controllers folder are "top" parent classes of a named project.
Then, 3 projects for instance:
1- FER: Final Evaluation Report (called by the YNY LMS, or all LMS owned by Yes'n'You)
2- Learnapp: called from https://m.learnapp.fr (cf. Learnapp project in bitbucket for source code)
3- Provalliance Json: called from any webapp that uses the course structure from the Provalliance LMS