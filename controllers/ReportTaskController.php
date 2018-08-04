<?php

class ReportTaskController extends CronosController {

    const MY_LOG_CATEGORY = 'controllers.ReportTaskController';
    const PARAM_SELECT_CUSTOMER = 'sel_customer';

    public $layout = '//layouts/top_menu';

    /**
     * Action to display the filter.
     */
    public function actionActivity() {
        $this->render('reportTask');
    }

    public function actionCost() {
        $this->render('reportCost');
    }

    /**
     * Style for border
     * @return array
     */
    private function getStyleBorder() {
        return array(
            'top' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM
            ),
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM
            ),
            'left' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM
            ),
            'right' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM
            )
        );
    }

    /**
     * Style for header
     * @return array
     */
    private function getStyleHeader() {
        return array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '000000')
            ),
            'font' => array(
                'name' => 'Arial',
                'size' => 12,
                'color' => array(
                    'rgb' => 'FFFFFF'
                )
            )
        );
    }

    /**
     * Export the cost into a Excel file.
     */
    public function actionExportCosts() {

        $aTypes = ExpenseType::getDataForDropDown();
        $aUserProjectTasks = ServiceFactory::createUserProjectTaskService()->findUserProjectTasksInTime($_GET['ExpenseSearch_dateIni'], $_GET['ExpenseSearch_dateEnd'], true, $_GET['ReportCost_companyId'], $_GET['ReportCost_projectId'], $_GET['ReportCost_worker']);

        $aUserProjectCosts = ServiceFactory::createProjectExpenseService()->findUserProjectCostsInTime($_GET['ExpenseSearch_dateIni'], $_GET['ExpenseSearch_dateEnd'], true, $_GET['ReportCost_companyId'], $_GET['ReportCost_projectId'], $_GET['ReportCost_worker']);
        
        // Turn off our amazing library autoload 
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        ini_set('include_path', ini_get('include_path') . ';/var/www/cronos-test.open3s.int/protected/extensions');

        /** PHPExcel */
        include 'PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include 'PHPExcel/Writer/Excel2007.php';

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Open3s");
        $objPHPExcel->getProperties()->setTitle("Report costes");
        $objPHPExcel->getProperties()->setSubject("Informe costes");
        $objPHPExcel->getProperties()->setDescription("");
        $objPHPExcel->setActiveSheetIndex(0);
        $oCurrentSheet = $objPHPExcel->getActiveSheet();

        $iFilaActual = 1;
        $oCurrentSheet->SetCellValue('A' . $iFilaActual, 'Cliente');
        $oCurrentSheet->getColumnDimension("A")->setAutoSize(true);
        $oCurrentSheet->SetCellValue('B' . $iFilaActual, 'Proyecto');
        $oCurrentSheet->getColumnDimension("B")->setAutoSize(true);
        $oCurrentSheet->SetCellValue('C' . $iFilaActual, 'Imputador');
        $oCurrentSheet->getColumnDimension("C")->setAutoSize(true);
        $oCurrentSheet->SetCellValue('D' . $iFilaActual, 'Fecha');
        $oCurrentSheet->getColumnDimension("D")->setAutoSize(true);
        $oCurrentSheet->SetCellValue('E' . $iFilaActual, 'Tipo gasto');
        $oCurrentSheet->getColumnDimension("E")->setAutoSize(true);
        $oCurrentSheet->SetCellValue('F' . $iFilaActual, 'Horas');
        $oCurrentSheet->getColumnDimension("F")->setAutoSize(true);
        $oCurrentSheet->SetCellValue('G' . $iFilaActual, 'Coste/Horas');
        $oCurrentSheet->getColumnDimension("G")->setAutoSize(true);
        $oCurrentSheet->SetCellValue('H' . $iFilaActual, 'Total Coste');
        $oCurrentSheet->getColumnDimension("H")->setAutoSize(true);
        $oCurrentSheet->getStyle("A" . $iFilaActual . ":H" . $iFilaActual)->applyFromArray($this->getStyleHeader());
        $oCurrentSheet->setAutoFilter("A" . $iFilaActual . ":E" . (count($aUserProjectTasks) + count($aUserProjectCosts)));

        $aCostReport = array();
        $iFilaActual++;
        $iPrimeraFila = $iFilaActual;
        
        
        //Coste por horas imputadas
        foreach ($aUserProjectTasks as $oUserProjectTask) {
            $aCostReport[$oUserProjectTask->companyName][$oUserProjectTask->projectName][$oUserProjectTask->workerName]
                    [PHPUtils::convertDateToShortString($oUserProjectTask->date_ini)]["Coste horas"]['duration'][] = $oUserProjectTask->getDuration();
            $aCostReport[$oUserProjectTask->companyName][$oUserProjectTask->projectName][$oUserProjectTask->workerName]
                    [PHPUtils::convertDateToShortString($oUserProjectTask->date_ini)]["Coste horas"]['cost'][] = $oUserProjectTask->workerCost;
        }
        
        //Coste por gastos del proyecto.
        foreach ($aUserProjectCosts as $oProjectExpense) {
            $aCostReport[$oProjectExpense->companyName][$oProjectExpense->projectName][$oProjectExpense->workerName]
                    [PHPUtils::removeHourPartFromDate($oProjectExpense->date_ini)][$aTypes[$oProjectExpense->costtype]]['duration'][] = '';
            $aCostReport[$oProjectExpense->companyName][$oProjectExpense->projectName][$oProjectExpense->workerName]
                    [PHPUtils::removeHourPartFromDate($oProjectExpense->date_ini)][$aTypes[$oProjectExpense->costtype]]['cost'][] = str_replace(",",".",$oProjectExpense->importe);
        }

        foreach ($aCostReport as $sCompanyName => $aCompanyDetail) {
            foreach ($aCompanyDetail as $sProjectName => $aProjectDetail) {
                foreach ($aProjectDetail as $sWorkerName => $aWorkerDetail) {
                    foreach ($aWorkerDetail as $sDateIni => $aDailyDetail) {
                        foreach ($aDailyDetail as $sCostType => $aCostDetail) {
                            for($i=0; $i < count($aCostDetail['duration']); $i++) {
                                $oCurrentSheet->SetCellValue('A' . $iFilaActual, $sCompanyName);
                                $oCurrentSheet->SetCellValue('B' . $iFilaActual, $sProjectName);
                                $oCurrentSheet->SetCellValue('C' . $iFilaActual, $sWorkerName);
                                $oCurrentSheet->SetCellValue('D' . $iFilaActual, $sDateIni);
                                $oCurrentSheet->SetCellValue('E' . $iFilaActual, $sCostType);
                                if ($aCostDetail['duration'][$i] != '') {
                                    $oCurrentSheet->SetCellValue('F' . $iFilaActual, "=ROUND(" . $aCostDetail['duration'][$i] . ", 2)");
                                    $oCurrentSheet->SetCellValue('G' . $iFilaActual, "=" . $aCostDetail['cost'][$i]);
                                    $oCurrentSheet->SetCellValue('H' . $iFilaActual, "=ROUND(F" . $iFilaActual . " * G" . $iFilaActual . ", 2)");
                                } else {
                                    $oCurrentSheet->SetCellValue('H' . $iFilaActual, "=" . $aCostDetail['cost'][$i]);
                                }
                                $iFilaActual++;
                            }
                        }
                    }
                }
            }
        }

        $oCurrentSheet->SetCellValue('G' . $iFilaActual, "Total");
        $oCurrentSheet->getStyle('G' . $iFilaActual . ":H" . $iFilaActual)->applyFromArray($this->getStyleHeader());
        $oCurrentSheet->SetCellValue('H' . $iFilaActual, "=SUBTOTAL(9, H" . $iPrimeraFila . ":H" . ($iFilaActual - 1) . ")");
        $oCurrentSheet->setTitle('Informe costes');

        // Redirect output to a clientâ€™s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="costes.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Report de actividad.
     */
    public function actionExportActivity() {

        //Por cada worker, recuperaremos los valores imputados.
        $workersHolidays = ServiceFactory::createUserService()->findWorkersWithProjectInTime($_GET['open_time'], $_GET['close_time'], true, "", Project::VACACIONES_ID);
        $workersTotalHours = ServiceFactory::createUserService()->findWorkersWithProjectInTime($_GET['open_time'], $_GET['close_time']);
        $workersBillableHours = ServiceFactory::createUserService()->findWorkersWithProjectInTime($_GET['open_time'], $_GET['close_time'], true);
        $workersHoursOpen3s = ServiceFactory::createUserService()->findWorkersWithProjectInTime($_GET['open_time'], $_GET['close_time'], true, Company::OPEN3S_ID);
        $companies = ServiceFactory::createCompanyService()->findCompaniesWithProjectInTime($_GET['open_time'], $_GET['close_time']);
        $projects = ServiceFactory::createProjectService()->findProjectInTime($_GET['open_time'], $_GET['close_time']);

        // Turn off our amazing library autoload 
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        ini_set('include_path', ini_get('include_path') . ';/var/www/cronos-test.open3s.int/protected/extensions');

        /** PHPExcel */
        include 'PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include 'PHPExcel/Writer/Excel2007.php';

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Open3s");
        $objPHPExcel->getProperties()->setTitle("Informe actividad");
        $objPHPExcel->getProperties()->setSubject("Informe actividad");
        $objPHPExcel->getProperties()->setDescription("");

        $objPHPExcel->setActiveSheetIndex(0);
        $oCurrentSheet = $objPHPExcel->getActiveSheet();
        $oCurrentSheet->getStyle("C3:D3")->applyFromArray($this->getStyleHeader());
        $oCurrentSheet->getStyle("C3:D6")->applyFromArray(
                array(
                    'borders' => $this->getStyleBorder()
                )
        );
        $oCurrentSheet->SetCellValue('C3', 'Datos Genericos a Introducir');
        $oCurrentSheet->SetCellValue('C4', 'personas');
        $oCurrentSheet->SetCellValue('D4', $_GET['personas']);
        $oCurrentSheet->SetCellValue('C5', 'dias laborables');
        $oCurrentSheet->SetCellValue('D5', $_GET['diaslaborales']);
        $oCurrentSheet->SetCellValue('C6', 'festivos');
        $oCurrentSheet->SetCellValue('D6', $_GET['festivos']);

        $oCurrentSheet->SetCellValue('C8', 'h laborables por persona');
        $oCurrentSheet->SetCellValue('D8', "=ROUND((D5-D6)*8,2)");

        $oCurrentSheet->SetCellValue('I4', 'h totales teoricas');
        $oCurrentSheet->SetCellValue('J4', "=D4*D8");
        $oCurrentSheet->SetCellValue('I5', 'horas realizadas');

        $oCurrentSheet->SetCellValue('I8', 'siempre >=100%');
        $oCurrentSheet->SetCellValue('J8', "=ROUND((J5/J4)*100,2)");
        $oCurrentSheet->getStyle("I8:J8")->applyFromArray($this->getStyleHeader());

        $iFilaActual = 10;
        $oCurrentSheet->SetCellValue('C' . $iFilaActual, 'empleados');
        $oCurrentSheet->SetCellValue('D' . $iFilaActual, 'h imputadas');
        $oCurrentSheet->SetCellValue('F' . $iFilaActual, 'vacaciones (dias)');
        $oCurrentSheet->SetCellValue('G' . $iFilaActual, 'h trabajadas');
        $oCurrentSheet->SetCellValue('H' . $iFilaActual, 'h facturables');
        $oCurrentSheet->SetCellValue('I' . $iFilaActual, '% actividad controlada');
        $oCurrentSheet->SetCellValue('J' . $iFilaActual, '% horas open3s (sin contar vacaciones)');
        $oCurrentSheet->SetCellValue('K' . $iFilaActual, 'horas open3s');

        $aWorkerBillable = array();
        foreach ($workersBillableHours as $oWorker) {
            $aWorkerBillable[$oWorker->name] = $oWorker->totalhours;
        }

        $aWorkerOpen3s = array();
        foreach ($workersHoursOpen3s as $oWorker) {
            $aWorkerOpen3s[$oWorker->name] = $oWorker->totalhours;
        }

        $aWorkerHolidays = array();
        foreach ($workersHolidays as $oWorker) {
            $aWorkerHolidays[$oWorker->name] = $oWorker->totalhours;
        }

        $iFilaActual++;
        $iCountWorkers = count($workersTotalHours);
        $iFilaTotal = $iFilaActual + $iCountWorkers;
        $oCurrentSheet->getColumnDimension('C')->setAutoSize(true);
        $oCurrentSheet->getColumnDimension('D')->setAutoSize(true);

        $oCurrentSheet->getStyle("C10:K" . ($iFilaTotal - 1))->applyFromArray(
                array(
                    'borders' => $this->getStyleBorder(),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'D3D3D3')
                    )
                )
        );

        $oCurrentSheet->getStyle('I' . $iFilaActual . ":I" . ($iFilaTotal - 1))->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '69F722')
                    )
        ));

        $oCurrentSheet->getStyle("C10:K10")->applyFromArray($this->getStyleHeader());

        $sFormulaSuma = "=0";
        //Mostramos cada uno de los users
        foreach ($workersTotalHours as $oWorker) {
            $oCurrentSheet->SetCellValue('C' . $iFilaActual, ($oWorker->name));
            $oCurrentSheet->SetCellValue('D' . $iFilaActual, $oWorker->totalhours);

            $fHoursHolidays = 0.00;
            if (isset($aWorkerHolidays[$oWorker->name])) {
                $fHoursHolidays = $aWorkerHolidays[$oWorker->name];
            }

            $oCurrentSheet->SetCellValue('F' . $iFilaActual, "=ROUND(" . $fHoursHolidays . "/8,2)");
            $oCurrentSheet->SetCellValue('G' . $iFilaActual, $oWorker->totalhours);
            $oCurrentSheet->SetCellValue('H' . $iFilaActual, "=ROUND((G" . $iFilaActual . "-(8*F" . $iFilaActual . ")),2)");
            $oCurrentSheet->SetCellValue('I' . $iFilaActual, "=ROUND((G" . $iFilaActual . "/D8)*100,2)");
            $oCurrentSheet->SetCellValue('J' . $iFilaActual, "=ROUND((K" . $iFilaActual . "/D" . $iFilaActual . ")*100,2)");

            $fHoursOpen3s = 0.00;
            if (isset($aWorkerOpen3s[$oWorker->name])) {
                $fHoursOpen3s = $aWorkerOpen3s[$oWorker->name];
            }

            $oCurrentSheet->SetCellValue('K' . $iFilaActual, $fHoursOpen3s);
            $sFormulaSuma .= "+BASE" . $iFilaActual;
            $iFilaActual++;
        }
        $oCurrentSheet->SetCellValue('C' . $iFilaActual, "Total");
        $oCurrentSheet->SetCellValue('D' . $iFilaActual, str_replace("BASE", "D", $sFormulaSuma));
        $oCurrentSheet->SetCellValue('K' . $iFilaActual, str_replace("BASE", "K", $sFormulaSuma));
        $oCurrentSheet->SetCellValue('G' . $iFilaActual, str_replace("BASE", "G", $sFormulaSuma));
        $oCurrentSheet->SetCellValue('H' . $iFilaActual, str_replace("BASE", "H", $sFormulaSuma));
        $oCurrentSheet->SetCellValue('J5', str_replace("BASE", "D", $sFormulaSuma));
        
        //Clientes horas imputadas.
        $iFilaActual = $iFilaActual + 5;
        $oCurrentSheet->SetCellValue('C' . $iFilaActual, 'clientes');
        $oCurrentSheet->SetCellValue('D' . $iFilaActual, 'h imputadas');
        $oCurrentSheet->SetCellValue('E' . $iFilaActual, '% h imputadas');
        $oCurrentSheet->getStyle("C" . $iFilaActual . ":E" . $iFilaActual)->applyFromArray($this->getStyleHeader());
        $iFilaActual++;

        $iCountCompanies = count($companies);
        $iFilaTotal = $iFilaActual + $iCountCompanies;
        $oCurrentSheet->getStyle("C" . $iFilaActual . ":E" . ($iFilaTotal - 1))->applyFromArray(
                array(
                    'borders' => $this->getStyleBorder(),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF00')
                    )
                )
        );
        $sFormulaSuma = "=0";
        foreach ($companies as $oCompany) {
            $oCurrentSheet->SetCellValue('C' . $iFilaActual, $oCompany->name);
            $oCurrentSheet->SetCellValue('D' . $iFilaActual, $oCompany->totalhours);
            $oCurrentSheet->SetCellValue('E' . $iFilaActual, "=ROUND((D" . $iFilaActual . "/D" . $iFilaTotal . ")*100,2)");
            $sFormulaSuma .= "+D" . $iFilaActual;
            $iFilaActual++;
        }
        
        //Finalizar
        $oCurrentSheet->SetCellValue('C' . $iFilaActual, "Total");
        $oCurrentSheet->SetCellValue('D' . $iFilaActual, $sFormulaSuma);
        $oCurrentSheet->setTitle('Informe actividad');
        
        
        //Categoria horas imputadas.
        $iFilaActual = $iFilaActual + 5;
        $oCurrentSheet->SetCellValue('C' . $iFilaActual, 'categoría');
        $oCurrentSheet->SetCellValue('D' . $iFilaActual, 'tipo');
        $oCurrentSheet->SetCellValue('E' . $iFilaActual, 'horas');
        $oCurrentSheet->getStyle("C" . $iFilaActual . ":E" . $iFilaActual)->applyFromArray($this->getStyleHeader());
        $iFilaActual++;
        
        $aCategories = array();
        foreach($projects as $oProject) {
            $projectTypeName = $oProject->category_name;
            $imputeTypeName = $oProject->imputetypeName;
            if (!isset($aCategories[$projectTypeName."&".$imputeTypeName])) {
                $aCategories[$projectTypeName."&".$imputeTypeName] = $oProject->totalhours;
            } else {
                $aCategories[$projectTypeName."&".$imputeTypeName] += $oProject->totalhours;
            }
        }
        
        $iCountCategories = count($aCategories);
        $iFilaTotal = $iFilaActual + $iCountCategories;
        $oCurrentSheet->getStyle("C" . $iFilaActual . ":E" . ($iFilaTotal - 1))->applyFromArray(
                array(
                    'borders' => $this->getStyleBorder(),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF00')
                    )
                )
        );
        foreach ($aCategories as $sCategory => $iTotalHours) {
            $aParts = split("&", $sCategory);
            $oCurrentSheet->SetCellValue('C' . $iFilaActual, $aParts[0]);
            $oCurrentSheet->SetCellValue('D' . $iFilaActual, $aParts[1]);
            $oCurrentSheet->SetCellValue('E' . $iFilaActual, $iTotalHours);
            $iFilaActual++;
        }
        
        //Cliente y proyecto horas imputadas.
        $iFilaActual = $iFilaActual + 5;
        $oCurrentSheet->SetCellValue('C' . $iFilaActual, 'cliente');
        $oCurrentSheet->SetCellValue('D' . $iFilaActual, 'proyecto');
        $oCurrentSheet->SetCellValue('E' . $iFilaActual, 'categoria');
        $oCurrentSheet->SetCellValue('F' . $iFilaActual, 'h imputadas');
        $oCurrentSheet->SetCellValue('G' . $iFilaActual, '% del total');
        $oCurrentSheet->getStyle("C" . $iFilaActual . ":G" . $iFilaActual)->applyFromArray($this->getStyleHeader());
        $iFilaActual++;
        
        $aProjectDetails = array();
        foreach($projects as $oProject) {
            //$oProject = new Project();
            $sReportKey = $oProject->company_name."#".$oProject->name."#".$oProject->category_name;
            if (!isset($aProjectDetails[$sReportKey])) {
                $aProjectDetails[$sReportKey] = $oProject->totalhours;
            } else {
                $aProjectDetails[$sReportKey] += $oProject->totalhours;
            }
        }
        
        $iCountProjects = count($aProjectDetails);
        $iFilaTotal = $iFilaActual + $iCountProjects;
        $oCurrentSheet->getStyle("C" . $iFilaActual . ":G" . ($iFilaTotal - 1))->applyFromArray(
                array(
                    'borders' => $this->getStyleBorder(),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF00')
                    )
                )
        );
        
        $sFormulaSuma = "=0";
        foreach ($aProjectDetails as $sDetail => $iTotal) {
            $aDetails = split("#", $sDetail);
            $oCurrentSheet->SetCellValue('C' . $iFilaActual, $aDetails[0]);
            $oCurrentSheet->SetCellValue('D' . $iFilaActual, $aDetails[1]);
            $oCurrentSheet->SetCellValue('E' . $iFilaActual, $aDetails[2]);
            $oCurrentSheet->SetCellValue('F' . $iFilaActual, $iTotal);
            $oCurrentSheet->SetCellValue('G' . $iFilaActual, "=ROUND((F" . $iFilaActual . "/F" . $iFilaTotal . ")*100,2)");
            
            $sFormulaSuma .= "+F" . $iFilaActual;
            $iFilaActual++;
        }
        
        //Finalizar
        $oCurrentSheet->SetCellValue('E' . $iFilaActual, "Total");
        $oCurrentSheet->SetCellValue('F' . $iFilaActual, $sFormulaSuma);

        // Redirect output to a client's web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="actividad.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}
