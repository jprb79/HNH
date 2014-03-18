// loader javascript
var homeurl = '/modules/' + module_name + '/';
var handlerurl = homeurl + 'ajax-attachments-handler.php';

function CreateAjaxRequest()
{
    var xh;
    if (window.XMLHttpRequest)
        xh = new window.XMLHttpRequest();
    else
        xh = new ActiveXObject("Microsoft.XMLHTTP");

    xh.open("POST", handlerurl, false, null, null);
    xh.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=utf-8");
    return xh;
}

var fileArray=[];

function ShowAttachmentsTable()
{
    var table = document.getElementById("filelist");
    while(table.firstChild)table.removeChild(table.firstChild);

    AppendToFileList(fileArray);
}
function AppendToFileList(list)
{
    var table = document.getElementById("filelist");

    for (var i = 0; i < list.length; i++)
    {
        var item = list[i];
        var row=table.insertRow(-1);
        row.setAttribute("fileguid",item.FileGuid);
        row.setAttribute("filename",item.FileName);
        row.setAttribute("filepath",item.OriginalFileName);
        var td1=row.insertCell(-1);
        td1.innerHTML="<img src='" + homeurl + "phpuploader/resources/circle.png' border='0'/>";
        var td2=row.insertCell(-1);
        td2.innerHTML=item.FileName;
        var td4=row.insertCell(-1);
        td4.innerHTML="[<a href='"+handlerurl+"?download="+item.OriginalFileName+"&name="+item.FileName+"'>download</a>]";
        var td4=row.insertCell(-1);
        td4.innerHTML="[<a href='javascript:void(0)' onclick='Attachment_Remove(this)'>remove</a>]";
    }
}

function Attachment_FindRow(element)
{
    while(true)
    {
        if(element.nodeName=="TR")
            return element;
        element=element.parentNode;
    }
}

function Attachment_Remove(link)
{
    var row=Attachment_FindRow(link);
    if(!confirm("Bạn có muốn xóa tập tin '"+row.getAttribute("filename")+"'?"))
        return;

    var guid=row.getAttribute("filepath");

    var xh=CreateAjaxRequest();
    xh.send("delete=" + guid);

    var table = document.getElementById("filelist");
    table.deleteRow(row.rowIndex);

    for(var i=0;i<fileArray.length;i++)
    {
        if(fileArray[i].OriginalFileName==guid)
        {
            fileArray.splice(i,1);
            break;
        }
    }
}

function CuteWebUI_AjaxUploader_OnPostback()
{
    var uploader = document.getElementById("myuploader");
    var guidlist = uploader.value;

    var xh=CreateAjaxRequest();
    xh.send("guidlist=" + guidlist);

    //call uploader to clear the client state
    uploader.reset();

    if (xh.status != 200)
    {
        alert("http error " + xh.status);
        setTimeout(function() { document.write(xh.responseText); }, 10);
        return;
    }

    var list = eval(xh.responseText); //get JSON objects

    fileArray=fileArray.concat(list);

    AppendToFileList(list);
}

function ShowFiles()
{
    var msgs=[];
    for(var i=0;i<fileArray.length;i++)
    {
        msgs.push(fileArray[i].OriginalFileName);
    }
    alert(msgs.join("\r\n"));
}
// end of uploader javascript