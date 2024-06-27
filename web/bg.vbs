Dim objFSO
Dim strScript
Dim strCurDir
Dim strDir

strScript = Wscript.ScriptFullName
Set objFSO = CreateObject("Scripting.FileSystemObject")
Set objScript = objFSO.GetFile(strScript)
strCurDir = objFSO.GetParentFolderName(objScript)
strDir = strCurDir & "\..\app"
Dim WsShell
Set WsShell = CreateObject("Wscript.Shell") 
WsShell.Run "cmd /k cd " & strDir & " & php console print-batch " & Wscript.Arguments(0) & " > NUL", 0, True
Set WsShell = Nothing
Wscript.Quit
