var currPage = 0;
	
function nextCheckPage(mnt)
{
	var bn = getSuperNodeByClass(mnt, "widget-content");
	var pn = getPaginationNodes(bn);
	
	var tp = pn[currPage];
	var pp = (pn[currPage + 1]) ? pn[currPage + 1] : null;

	if(pp)
	{
		addClass(tp, "checkBoxInvisible");
		removeClass(pp, "checkBoxInvisible");

		currPage++;
		updatePageNumber(bn, currPage + 1);
	}
}

function prevCheckPage(mnt)
{
	var bn = getSuperNodeByClass(mnt, "widget-content");
	var pn = getPaginationNodes(bn);

	var tp = pn[currPage];
	var pp = (pn[currPage - 1]) ? pn[currPage - 1] : null;
	
	if(pp)
	{
		addClass(tp, "checkBoxInvisible");
		removeClass(pp, "checkBoxInvisible");
		
		currPage--;
		updatePageNumber(bn, currPage + 1);
	}
}

function updatePageNumber(mnt, pg)
{
	var pn = mnt.getElementsByClassName("pageNumberContainer")[0];
	clearElement(pn);

	pn.appendChild(document.createTextNode(pg));

}

function getSuperNodeByClass(mnt, cls)
{
	var tst = mnt;
	var cn = null;

	while((tst) && (cn != cls))
	{
		tst = tst.parentNode;
		cn = (tst.getAttribute) ? tst.getAttribute("class") : null;
	}
	
	return tst;
}

function clearElement(mnt)
{
	var nds = mnt.childNodes;
	
	while(nds[0])
	{
		mnt.removeChild(nds[0]);
	}
}

function addClass(mnt, cla)
{
	removeClass(mnt, cla);
	var cn = mnt.getAttribute("class");
	
	if(!cn)
	{
		cn = "";
	}
	
	mnt.setAttribute("class", cn + " " + cla);
}

function removeClass(mnt, cla)
{
	var cn = mnt.getAttribute("class");
	
	if(!cn)
	{
		cn = "";
	}
	
	mnt.setAttribute("class", cn.replace(cla, "").trim());
}

function getPaginationNodes(mnt)
{
	return getNodesByClass(mnt, "productWidgetCheckArea");
}

function getNodesByClass(mnt, cls)
{
	var cldn = mnt.childNodes;
	var ret = new Array();

	for(var i in cldn)
	{
		if(!cldn[i].getAttribute)
		{
			continue;
		}

		var cn = cldn[i].getAttribute("class");

		if(cn && (cn.indexOf(cls) != -1))
		{
			ret.push(cldn[i]);
		}
	}

	return ret;
}