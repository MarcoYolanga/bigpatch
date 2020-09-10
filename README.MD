This PHP script creates a folder grouping the  git changed files of your project, and allows you to upload these files to your production server folders

### Bigpatch creates patches ad uploads them!

# Dependances
> Windows
> Git available in the cmd
> PHP available in the cmd
> The php-ftp extension
Tested with php 7.3

# Installing Bigpatch
Clone Bigpatch and add his path to System variable %PATH%

# Configuring Bigpatch
Configure your **projects** as batch scripts in the projects dir (copying the sample) and add your servers as json files in **servers** (each remote ftp directory, even in the same host needs it's own json file)

# Running Bigpatch
After you have modified your code, run **git add .** to stage your edits,  then run **bigpatch**. The script will ask you which Project you want to update, then grabs all the modified files and folders from the project's local directory, asks you to chose which files you want to upload (remembering your choiches for next patches) and then on which server (or more using * wildcard in server selection) you want them uploaded. Finally, you can commit the changes.

# Autoanswers
Bigpatch will learn your answers never asking them again!
Delete or edit the generated file **answers.json**, the syntax is {"**name_of_file**":"**answer**"} where answer can be **"e"** = exclude or **a** = add to the next patches
