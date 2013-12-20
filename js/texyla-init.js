$.texyla.setDefaults({
	baseDir: '{{$baseUri}}js/texyla',
	previewPath: '{{$previewPath}}',
	filesPath: '{{$filesPath}}',
	filesUploadPath: '{{$filesUploadPath}}',
	filesMkDirPath: '{{$filesMkDirPath}}',
	filesRenamePath: '{{$filesRenamePath}}',
	filesDeletePath: '{{$filesDeletePath}}',
	language: 'en'
});

$(function () {
	$(".texyla").texyla({
		toolbar: [
			'h2', 'h3', 'h4',
			null,
			'bold', 'italic', 'del',
			null,
			'codeInline',
			null,
			'center', ['left', 'right', 'justify'],
			null,
			'ul', 'ol', ["olAlphabetSmall", "olAlphabetBig", "olRomans", "olRomansSmall"],
			null,
			/*{ type: "label", text: "Insert"}, */'link', 'img', 'table', 'emoticon', 'symbol',
			null,
			'color', 'textTransform',
//			null,
//			'files', 'youtube', 'gravatar',
			null,
			'div', ['html', 'blockquote', 'text', 'comment', 'code'],
//			null,
//			'code',	['codeHtml', 'codeCss', 'codeJs', 'codePhp', 'codeSql'], 'codeInline',
			null,
			{ type: "label", text: "Other"}, ['sup', 'sub', 'acronym', 'hr', 'notexy', 'web']

		],
		texyCfg: "admin",
		bottomLeftToolbar: ['edit', 'preview'/*, 'htmlPreview'*/],
		buttonType: "span",
		tabs: true
	});

	$.texyla({
		buttonType: "button"
	});

});