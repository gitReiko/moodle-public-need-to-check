
function hide_or_show_block(id)
{
    var block = document.getElementById(id).style; 

    if(block.display == "none") block.display = "block";
    else block.display = "none";
}