## Add salary field to WP Job Manager plugin.

Using the example shown here https://github.com/Astoundify/wp-job-manager-fields I've modified the class to create a salary field.

The salary range inputs are also added to the search form.

Either upload this file as a plugin, include it in your own, or add it to a child theme. The inline documentation does a pretty solid job of explaining what is going on.

#### My modifications include no styling for the search fields

Something like this should about do it:


  .job_listings .salary li{width:100%;padding:0}
  
  .job_listings .salary input,
  .job_listings .salary select{float:left;width:31.5%;margin-top:20px}
  
  .job_listings .salary .salary_to{margin-left:1em}
  
  .job_listings .salary .salary_what{float:right}

