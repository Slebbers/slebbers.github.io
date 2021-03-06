<?php
	try {
		// connect to database
		include 'dbConnect.php';

		$query = "SELECT Checklist.ChecklistID, ChecklistItem.ChecklistItemID, ChecklistItem.ChecklistItem, Equipment.EquipmentID, Equipment.EquipmentName, Equipment.LastInspection, Equipment.NextInspection, Equipment.Status, EquipmentItem.IsChecked
				FROM Checklist
				INNER JOIN ChecklistItem ON Checklist.ChecklistID = ChecklistItem.ChecklistID
				INNER JOIN Equipment ON ChecklistItem.ChecklistID = Equipment.ChecklistID
				INNER JOIN EquipmentItem ON ChecklistItem.ChecklistItemID = EquipmentItem.ChecklistItemID";
		$checklist = mysqli_query($db, $query);
		
		
		$rows = array();

		while($data = mysqli_fetch_assoc($checklist)) {
			$rows[] = $data;
		}

		$groupedJson = array();
		// Encode then decode so we can access as a class
		$encoded = json_encode($rows);
		$temp = json_decode($encoded);

		// Here we build our JSON so that it groups things by IDs, otherwise we 
		// will have loads of repeats and have to parse a lot of JSON on the app end
		// making it very complicated
		foreach($temp as $row) {
			if(!array_key_exists($row->ChecklistID, $groupedJson)) {
				$jsonItem = new stdClass();
				$jsonItem->ChecklistID = $row->ChecklistID;
				$jsonItem->EquipmentID = $row->EquipmentID;
				$jsonItem->EquipmentName = $row->EquipmentName;
				$jsonItem->LastInspection = $row->LastInspection;
				$jsonItem->NextInspection = $row->NextInspection;
				$jsonItem->Status = $row->Status;
				$jsonItem->ChecklistItems = array();
				$groupedJson[$row->ChecklistID] = $jsonItem;
			}

			$checklistItem = new stdClass();
			$checklistItem->ChecklistItemID = $row->ChecklistItemID;
			$checklistItem->ChecklistItem = $row->ChecklistItem;
			$checklistItem->IsChecked = $row->IsChecked;

			$groupedJson[$row->ChecklistID]->ChecklistItems[] = $checklistItem;
		}
		$groupedJson = array_values($groupedJson);
		echo json_encode($groupedJson);
	} catch(Exception $e) {
		echo "Error: " . $e;
	}
?>