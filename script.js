
function hide_or_show_block(id)
{
    var block = document.getElementById(id).style; 
    var teacher = document.getElementById("teacher"+id); 

    if(block.display == "none")
    {
        block.display = "block";
        teacher.className = "chekingTeacher vertical-node";
    }
    else
    {
        block.display = "none";
        teacher.className = "chekingTeacher horizontal-node";
    }
}