tinymce.PluginManager.add('tstpre', function(editor,url) {
	function showDialog() {
		var	selection = editor.selection, selectionNode = selection.getNode(), selectedCode, selected = false;
		var data = {};
		if (selectionNode.nodeName.toLowerCase() == 'pre') {
			selected = true;
			selectedCode = $(selectionNode).html();
			selectedCode = selectedCode.replace(/\&lt\;/gi,"<").replace(/\&gt\;/gi,">");
		} else {
			selectedCode = selection.getContent({format : 'text'});
		}
		data.code = selectedCode;
		if (data.code == '&nbsp;') {data.code = '';}
		function onSubmitFunction(e) {
			var code = e.data.code;
			code = code.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
			var Elmt = editor.dom.create('pre',	{}, code);
			if (selected) {
				editor.dom.replace(Elmt, selectionNode);
			} else {
				editor.insertContent(editor.dom.getOuterHTML(Elmt)+'<br>');
			}
		}
		var win = editor.windowManager.open({
			title: 'Code / Citation',
			data: data,
			minWidth: 450,
			body: [{name: 'code', type: 'textbox', minHeight: 200, multiline: true}],
			onsubmit: onSubmitFunction
		});
	}
	editor.addButton('tstpre', {
		icon: 'code',
		tooltip: 'Insérer/Modifier un code ou une citation',
		onclick: showDialog,
		stateSelector: 'pre'
	});
});