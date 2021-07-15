var env = [
    "INDESING_SERVER_VERSION = " + app.version,
    "COMPUTERNAME = " + $.getenv("COMPUTERNAME"),
    "USERDOMAIN = " + $.getenv("USERDOMAIN"),
    "USERNAME = " + $.getenv("USERNAME"),
    "OS = " + $.getenv("OS"),
    "NUMBER_OF_PROCESSORS = " + $.getenv("NUMBER_OF_PROCESSORS"),
    "PROCESSOR_ARCHITECTURE = " + $.getenv("PROCESSOR_ARCHITECTURE"),
];

var myDocument = app.documents.add();

var defaultDocName = "SaveDocumentAsThisName";
var docName = app.scriptArgs.isDefined("doc_name") ? app.scriptArgs.getValue("doc_name") : defaultDocName;


var defaultDelay = 5;
var delayInSec = app.scriptArgs.isDefined("delay") ? parseInt(app.scriptArgs.getValue("delay")) : defaultDelay;
sleep(delayInSec * 1000);


myDocument.save(File("/c/tmp/" + docName + ".indd"));
myDocument.close();

var returnValue;
returnValue = [
    'foo',
    "bar",
    "only arrays and strings will pass through",
    [1, 2, 3, ['nested arrays are fine']],
    {'foo': 'objects will not pass through', 'bar': 'neither will this'}
];
returnValue = "abc";
returnValue = ["a", "b", "c"];
returnValue = env;

//return something
returnValue;

function sleep(delay) {
    var start = new Date().getTime();
    while (new Date().getTime() < start + delay) ;
}
