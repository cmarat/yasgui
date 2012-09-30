package com.data2semantics.yasgui.client.tab.results.output;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;
import com.data2semantics.yasgui.client.View;
import com.data2semantics.yasgui.client.helpers.Helper;
import com.data2semantics.yasgui.client.tab.results.input.SparqlResults;
import com.data2semantics.yasgui.shared.Prefix;
import com.smartgwt.client.util.StringUtil;
import com.smartgwt.client.widgets.HTMLPane;

public class SimpleGrid extends HTMLPane {
	private View view;
	private HashMap<String, Prefix> queryPrefixes = new HashMap<String, Prefix>();
	private ArrayList<String> variables;
	private ArrayList<HashMap<String, HashMap<String, String>>> solutions;
	private String html;

	public SimpleGrid(View view, SparqlResults sparqlResults) {
		this.view = view;
		setWidth100();
		setHeight100();
		queryPrefixes = Helper.getPrefixesFromQuery(view.getSettings().getSelectedTabSettings().getQueryString());
		variables = sparqlResults.getVariables();
		solutions = sparqlResults.getBindings();
		drawTable();
		setContents(html);
		
	}

	private void drawTable() {
		html = "<table class=\"simpleTable\">";
		drawHeader();
		drawRows();

		html += "</table>";
	}

	private void drawHeader() {
		html += "<thead><tr class=\"simpleTable\">";
		for (String variable: variables) {
			html += "<th>" + StringUtil.asHTML(variable) + "</th>";
		}
		html += "</tr></thead>";
	}

	private void drawRows() {
		html += "<tbody>";
		for (HashMap<String, HashMap<String, String>> bindings: solutions) {
			html += "<tr>";
			for (String variable: variables) {
				html += "<td>";
				if (bindings.containsKey(variable)) {
					HashMap<String, String> binding = bindings.get(variable);
					if (binding.get("type").equals("uri")) {
						String uri = binding.get("value");
						html += "<a href=\"" + uri + "\" target=\"_blank\">" + StringUtil.asHTML(getShortUri(uri)) + "</a>";
					} else {
						html += StringUtil.asHTML(binding.get("value"));
					}
				} else {
					html += "&nbsp;";
				}
				html += "</td>";
			}
			html += "</tr>";
		}
		html += "</tbody>";
	}

	@SuppressWarnings("unused")
	private View getView() {
		return this.view;
	}

	/**
	 * Check for a uri whether there is a prefix defined in the query.
	 * 
	 * @param uri
	 * @return Short version of this uri if prefix is defined. Long version
	 *         otherwise
	 */
	private String getShortUri(String uri) {
		for (Map.Entry<String, Prefix> entry : queryPrefixes.entrySet()) {
			String prefixUri = entry.getKey();
			if (uri.startsWith(prefixUri)) {
				uri = uri.substring(prefixUri.length());
				uri = entry.getValue().getPrefix() + ":" + uri;
				break;
			}
		}
		return uri;
	}
}