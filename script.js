
function hide_or_show_block(id, rowtype)
{
    var block = document.getElementById(id).style; 
    var container = document.getElementById(rowtype+id); 

    if(block.display == "none")
    {
        block.display = "block";
        container.className = "chekingTeacher vertical-node";
    }
    else
    {
        block.display = "none";
        container.className = "chekingTeacher horizontal-node";
    }
}