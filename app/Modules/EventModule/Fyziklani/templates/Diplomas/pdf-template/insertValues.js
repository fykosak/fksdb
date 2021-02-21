// placeholder -- variables are to be loaded from FKSDB
let teamName = "epicFofTeamName";
var teamMembers = ["Jára Cimrman", "Jan Žižka", "Ernest Mach", "Eliška Krásnohorská", "Maximilián Podkrušnohorský"];
var lang = "cs";
var schoolName = "Wichterlovo G, Ostrava-Poruba, p. o.";
var cat = "A";
var place_tot = "25";
var place_cat = "14";


window.onload = function(teamName, teamMembers, schoolName, lang, teamPlace_cat, teamPlace_tot, eventDate, eventName, event_website){
    // if (lang == "cs"){
    //     teamEnumerate = "ve složení " + teamMembers.toString() + "ze školy " + schoolName;
    // }
    var teamName = "mujTYm";
    var teamEnumerate = "ve složení AA bb CC";

    document.getElementById('team').innerHTML = teamName;
    document.getElementById('team_members').innerHTML = teamEnumerate;
};

