Dim objShell, objFSO, outputFile, phpScriptPath, url, cmd

' Create Shell and FileSystemObject
Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' Define PHP script path and URL
phpScriptPath = "D:\\ExtendScriptModules\\GET_url.php"
url = "https://jsonplaceholder.typicode.com/posts/1" ' Replace with your URL

' Define command
outputFile = objShell.ExpandEnvironmentStrings("%TEMP%") & "\\php_output.txt"
cmd = "php " & phpScriptPath & " " & url & " > " & outputFile

' Execute command
objShell.Run cmd, 0, True

' Read and output the result
If objFSO.FileExists(outputFile) Then
    Dim outputFileObj
    Set outputFileObj = objFSO.OpenTextFile(outputFile, 1)
    WScript.Echo outputFileObj.ReadAll()
    outputFileObj.Close()
End If
