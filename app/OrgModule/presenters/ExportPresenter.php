<?php

namespace OrgModule;

use IResultsModel;
use ModelContest;
use ModelPerson;
use ModelPostContact;

class ExportPresenter extends BasePresenter {

    /**
     * Very ineffective solution that provides data in
     * specified format.
     */
    public function renderOvvp() {
        $modelFactory = $this->getService('resultsModelFactory');
        $serviceContestant = $this->getService('ServiceContestant');


        $model = $modelFactory->createCumulativeResultsModel($this->getSelectedContest(), $this->getSelectedYear());
        $this->template->data = array();

        foreach ($model->getCategories() as $category) {
            $rows = array();
            $model->setSeries(array(1, 2, 3, 4, 5, 6));

            $header = $model->getDataColumns($category);
            $sumCol = 0;
            foreach ($header as $column) {
                if ($column[IResultsModel::COL_DEF_LABEL] == IResultsModel::LABEL_SUM) {
                    break;
                }
                $sumCol++;
            }

            $datas = array();
            foreach ($model->getData($category) as $data) {
                if ($data->sum !== null) {
                    $datas[] = $data;
                }
            }

            foreach ($datas as $data) {
                $ctid = $data->ct_id;

                $row = array();
                $contestant = $serviceContestant->findByPrimary($ctid);
                $person = ModelPerson::createFromTableRow($contestant->person);

                // jména                
                $row[] = $person->other_name;
                $row[] = $person->family_name;

                // adresa dom
                $contacts = $person->getPostContacts();
                $bestMatch = null;
                foreach ($contacts as $contact) {
                    if ($contact->type == 'D') {
                        $bestMatch = $contact;
                        break;
                    } else {
                        $bestMatch = $contact;
                    }
                }
                if ($bestMatch) {
                    $bestMatch = ModelPostContact::createFromTableRow($bestMatch);
                    $address = $bestMatch->getAddress();
                    $parts = explode(' ', $address->target);

                    $row[] = implode(' ', array_slice($parts, 0, count($parts) - 1));
                    $row[] = $parts[count($parts) - 1];
                    $row[] = $address->city;
                    $row[] = $address->postal_code;
                    $row[] = ($address->region->country_iso == 'EP') ? '' : strtolower($address->region->country_iso);
                } else {
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                }

                // škola
                if ($contestant->school) {
                    $row[] = $contestant->school->name_abbrev;
                    $row[] = $contestant->school->izo;
                } else {
                    $row[] = '';
                    $row[] = '';
                }

                // rok maturity
                if ($contestant->study_year !== null) {
                    $year = $this->getSelectedYear();
                    $studyYear = ($contestant->study_year >= 1 && $contestant->study_year <= 4) ? $contestant->study_year : ($contestant->study_year - 9);
                    if ($contestant->contest_id == ModelContest::ID_FYKOS) {
                        $row[] = 1991 + $year - $studyYear;
                    } else if ($contestant->contest_id == ModelContest::ID_VYFUK) {
                        $row[] = 2015 + $year - $studyYear;
                    }
                } else {
                    $row[] = '';
                }

                // e-mail
                if ($person->getLogin() && $person->getLogin()->email) {
                    $row[] = $person->getLogin()->email;
                } else {
                    $row[] = '';
                }

                // pořadí
                $row[] = (($data->from == $data->to) ? $data->from : ($data->from . '-' . $data->to)) . '/' . count($datas);

                // body
                $row[] = $data->sum . '/' . $header[$sumCol][IResultsModel::COL_DEF_LIMIT];


                // append
                if ($data->sum !== null) {
                    $rows[] = $row;
                }
            }
            $this->template->data[$category->id] = $rows;
        }
    }

}
