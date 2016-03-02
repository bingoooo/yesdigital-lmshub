CKEDITOR.dialog.add( 'noteDialog', function ( editor )
{
	var listeNotes = ['A','A#','B','C','C#','D','D#','E','F','F#','G','G#'];
	var listeVariantes = ['','m','5','aug','dim','sus2','sus4','6','m6','7','m7','Maj7','7sus4','m7/b5','9','add9','m9','11','13'];
	var pathToImages = this.path+"images/accords/accordsFull.jpg";
	var htmlStart = '<div style="background:url(\'' + pathToImages + '\') ';
	var htmlEnd = ' no-repeat;display:inline-block;margin:5px;height:80px;width:80px;"></div>';
	var elPos = ['0','-80px','-160px','-240px','-320px','-400px','-480px','-560px','-640px','-720px','-800px','-880px','-960px','-1040px','-1120px','-1200px','-1280px','-1360px','-1440px'];
	var content = createContent(editor,listeNotes,listeVariantes,elPos,htmlStart,htmlEnd);
	
	definitions = {
		title : 'Note selector',
		minWidth : 470,
		minHeight : 400,
		contents : 
		[
			{
				id:'tab0',
				label:'Help',
				accessKey : 'F1',
				elements:
				[
					{
						type:'hbox',
						widths : ['50%', '50%'],
						children:
						[
							{
								type:'button',
								id:'start',
								label:'Start Sentence',
								onClick : function(){
									editor.insertHtml('<div class="phrase"></div>');
									CKEDITOR.dialog.getCurrent().hide();
								}
							}
						]
					},
					{
						type : 'html',
						html : '<p>Currently on beta version so follow this to make it work!<br /><br /></p><p>start a sentence by clicking on the "sentence" button above,<br />type in your text for the song then place cursor right before the letter you want the note to appear,<br />then add the note by clicking on it in the dialog box.<br />To finish just click on "insert a paragraph" button included in editor (right side of dotted red line). Enjoy!!</p>'
					}
				]
			},
			content[0],
			content[1],
			content[2],
			content[3],
			content[4],
			content[5],
			content[6],
			content[7],
			content[8],
			content[9],
			content[10],
			content[11],
			content[12],
			{
				id : 'tab8',
				label : 'About',
				accessKey : 'F2',
				elements :
				[
					{
						type : 'html',
						html : '<p>Autor : Christophe Tetard (BoN crew)<br /><br />Version : 0.1 beta</p>'
					}
				]
			}
		]
		};
	
	return definitions;
});

insertNote = function(note,editor,posX,posY)
{
	var html = '<div class="note"><pre class="basenote ' + note.label + '" style="background-position:' + posX + ' ' + posY + '">'+note.label+'</pre></div>';
	editor.insertHtml(html);
	CKEDITOR.dialog.getCurrent().hide();
};

createContent = function(editor,listeN,listeV,pos,start,end)
{
	var definition = new Array();
	var chords = new Array();
	var hboxs = new Array();
	var child = [];
	
	for(var i=0;i<listeN.length;i++)
	{
		//create chords
		for(var j=0;j<listeV.length;)
		{
			for(var k=0;k<5;k++,j++)
			{
				if(j>listeV.length){
					chords.push({type:'html',html:'<div></div>'});
				}
				else{
					chords.push(
						{
							type:'html',
							id:listeN[i]+listeV[j],
							label:listeN[i]+listeV[j],
							html:start+pos[j]+' '+pos[i]+end,
							onClick:function()
							{
								insertNote(this,editor,pos[j],pos[i]);
							}
						});
				}
			}
			//create hbox
			hboxs.push(
				{
					type:'hbox',
					widths:"['20%','20%','20%','20%','20%']",
					children:
					[
						chords[0],
						chords[1],
						chords[2],
						chords[3],
						chords[4]
					]
				});
			chords = new Array();
		}
		//create tab
		definition.push(
			{
				id:'tab'+listeN[i],
				label:listeN[i],
				elements:
				[
					hboxs[0],
					hboxs[1],
					hboxs[2],
					hboxs[3]
				]
			});
		hboxs = new Array();
	}

	return definition;
};