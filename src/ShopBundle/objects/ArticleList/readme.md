# ArticleList – Post Search Filter Logik

## Grundprinzip

Der **Post Search Filter** greift ausschließlich auf den Hauptfilter zu, **nicht** auf einen Fallback.

**Vorteil:**  
Der Post-Search-Filter muss nur wissen, **welche Query** verwendet wird – das vereinfacht die Logik.

---

## Ablauf & Interaktion

### 1. Einschränkung durch Konfiguration

- Das **Post-Search-Filter-Modul** muss auf eine Liste einschränkbar sein.
- Es liest die nötigen Filterdaten aus der **Konfiguration**.

### 2. Liste prüft auf Filter-Konfiguration

- Die **Liste prüft**, ob ein Post-Search-Filter konfiguriert ist.
- Falls ja, **holt sie sich den State** vom Filtermodul.

### 3. Für den State wird der **Spot** benötigt.

---

## Beispielhafte Logik in Code

```php
$postSearchFilterApi = new PostSearchFilterApi();
$postSearchFilter = $postSearchFilterApi->getPostSearchFilter();

$stateHash = $postSearchFilter->getStateHash();
$queryString = $postSearchFilter->getFilteredQuery($sourceQuery);

$listQuery = $postSearchFilterApi->getListQuery();
$queryString = $postSearchFilter->getFilteredQuery($sourceQuery);

// Danach: Filteranzeige anzeigen
