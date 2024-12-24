post search filter grieft immer nur auf den haupt filter -> nicht auf den fallback
  -> vorteil - mein post search filter muss nur wissen um welche query es geht

ablauf:
	* post-search-filter module muss auf eine Liste einschränkbar sein. aus der Konfiguration kann es sich den filter suchen
	* die liste muss schauen ob ein post-search-filter konfiguriert ist -> und wenn ja, sich von dem den state holen
	* für den state brauche ich den spot...


	postSearchFilterApi = new PostSearchFilterApi();
	postSearchFilter = postSearchFilterApi->getPostSearchFilter()
	stateHash = postSearchFilter->getStateHash()
	queryString = postSearchFilter->getFilteredQuery(sourceQuery)



	listQuery = postSearchFilterApi->getListQuery()
	queryString = postSearchFilter->getFilteredQuery(sourceQuery)
	// ... show filter

