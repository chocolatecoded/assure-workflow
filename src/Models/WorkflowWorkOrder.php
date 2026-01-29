<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This is a extension model of the work_orders table
 * since workflow is attached to the work_orders, it's
 * easier to make a new Model for work_orders table.
 */
class WorkflowWorkOrder extends Model
{
    public const PERMIT_APPROVED = 'Permit Approved';

    protected $table = 'work_orders';

    public function company()
    {
        return $this->belongsTo(WorkflowCompany::class, 'client_code', 'company_id');
    }

    public function workOrderTechnicians()
    {
        return $this->hasMany('App\Models\WorkOrdersTechnicians', 'work_order_id');
    }

    /**
     * copy from WorkOrders model from root project, update this code if necessary or cleanup once
     * requirements are clear
     *
     * @param $user
     * @param $formId
     * @param $submissionId
     * @param $dateTime
     * @param $formType
     * @param $authorsationType
     * @param $authoriserId
     * @param $dateTimeTz
     * @param $groupId
     * @return mixed
     */
    public function recordApproval($user, $formId, $submissionId, $dateTime, $formType, $authorsationType = null, $authoriserId = null, $dateTimeTz, $groupId = null)
    {
        //With Permit// OnSite // Permit Approved //Permit Approved
        //With Permit// OffSite // Permit Submitted //Awaiting Permit Approval
        //Without Permit// OnSite // PRA Approved //PRA Approved
        //Without Permit// OffSite // PRA Submitted //Awaiting Permit Approval
        $authorsationType = 'Unoccupied';

        $autoApproveForms = [70722159676, 73025102637, 70872450681, 63191543642, 81061637697, 2881639086, 891459692, 91483355635, 92192634649, 2881712078, 894509533];

        // Prevent group pra change status when there is no work_order_status
        $isBulkBeforeStartJob = !($groupId && $this->work_order_status || !$groupId);

        $auditWaitingApproval = $groupId ? self::BULK_PRA_REQUEST : self::APPROVE_SUBMIT;
        $auditApprove = $groupId ? self::BULK_PRA_APPROVED : self::PERMIT_APPROVED;


        $formSubmission = $this->recordFormSubmit($user, $formId, $submissionId, $dateTime, $formType, $authorsationType, $authoriserId, $dateTimeTz, $groupId);

        // normal work flow
        if ($isBulkBeforeStartJob === false) {
            if (in_array($formId, $autoApproveForms)) {
                $this->logActivity($user->id, $dateTime, $auditApprove, $dateTimeTz);
                $this->work_order_status = self::PERMIT_APPROVED;
            } elseif (stripos($authorsationType, 'Unoccupied') !== false) {
                if ($this->doesItNeedApproval($formSubmission)) {
                    $this->logActivity($user->id, $dateTime, $auditWaitingApproval, $dateTimeTz);
                } else {
                    $this->logActivity($user->id, $dateTime, $auditApprove, $dateTimeTz);
                    $this->doSpecialApproval($formSubmission);
                }


                if ($this->work_order_status != self::PO_INCREASE_AWAITING) {
                    $this->work_order_status = $this->doesItNeedApproval($formSubmission) ? self::APPROVE_SUBMIT : self::PERMIT_APPROVED;
                }
            } else {
                if ($this->hasPermitForm()) {
                    $this->logActivity($user->id, $dateTime, $auditApprove, $dateTimeTz);
                    $this->work_order_status = self::PERMIT_APPROVED;
                } else {
                    $this->logActivity($user->id, $dateTime, 'PRA Approved', $dateTimeTz);
                    $this->work_order_status = self::PERMIT_APPROVED;
                }
            }

            // Bulk PRA before start job
        } else {
            if (in_array($formId, $autoApproveForms)) {
                $this->logActivity($user->id, $dateTime, self::BULK_PRA_APPROVED, $dateTimeTz);
                $this->bulk_pra_status = self::BULK_PRA_APPROVED;
            } elseif (stripos($authorsationType, 'Unoccupied') !== false) {
                if (!$this->doesItNeedApproval($formSubmission)) {
                    $this->logActivity($user->id, $dateTime, self::BULK_PRA_APPROVED, $dateTimeTz);
                    $this->doSpecialApproval($formSubmission);
                }

                if ($this->work_order_status != self::PO_INCREASE_AWAITING) {
                    // we already have bulk pra before this state
                    $this->bulk_pra_status = $this->doesItNeedApproval($formSubmission) ? self::BULK_PRA_REQUEST : self::BULK_PRA_APPROVED;
                }
            } else {
                if ($this->hasPermitForm()) {
                    $this->logActivity($user->id, $dateTime, self::BULK_PRA_APPROVED, $dateTimeTz);
                    $this->bulk_pra_status = self::BULK_PRA_APPROVED;
                } else {
                    $this->logActivity($user->id, $dateTime, 'PRA Approved', $dateTimeTz);
                    $this->bulk_pra_status = self::BULK_PRA_APPROVED;
                }
            }
        }

        $this->save();

        return $formSubmission;
    }


    public function recordFormSubmit($user, $formId, $submissionId, $dateTime, $formType, $authorsationType = null, $authoriserId = null, $dateTimeTz = null, $groupId = null)
    {
        $w = new WorkOrdersForm();
        $w->user_id = $user->id;
        $w->work_order_id = $this->id;
        $w->submitted_at = date('Y-m-d H:i:s', strtotime($dateTime));
        $w->submitted_at_tz = $dateTimeTz;
        $w->form_id = $formId;
        $w->submission_id = $submissionId;
        $w->form_type = $formType;
        $w->authorsation_type = $authorsationType;
        $w->authoriser_id = $authoriserId;
        $w->pra_group_id = $groupId;

        $w->save();

        //Get Data
        $c = new \GuzzleHttp\Client();
        $r = $c->get(sprintf('%s%s', config('app.composerUri'), $formId));
        $b = $r->getBody();

        $js = json_decode((string) $b);
        $w->form_title = $js->form->title;
        $w->save();

        $path = sprintf('/%s/%s.%s.%s.csv', $this->getFolderPath(), $formType, $formId, $submissionId);

        //@TODO potentially move to queue, but not yet.
        $w->downloadCsvPdf();

        info($path);
        $data = $w->getCsvData();
        switch ($formId) {
            case 73531547665: // Restricted Plumbing Works Checklist
                $client = $this->company()->first();
                $email = $client->work_order_email_pra_submitted;

                $options = [
                    'plumbing' => 'Restricted Plumbing Works',
                    'gas' => 'Restricted Gas Fitting Works',
                    'drain laying' => 'Restricted Drain Laying Works',
                ];

                if ($email) {
                    foreach ($options as $name => $opt) {
                        if (preg_match("/$opt/", $data[1])) {
                            $subject = "Restricted {$name} Work notification for Work Order {$this->work_order_no}";
                            $emailJob = new Jobs\Email('emails.restricted_works', $subject, $email, [
                                'type' => $name,
                                'user' => $user,
                                'workOrder' => $this,
                                'comments' => $data[2],
                            ]);
                            dispatch($emailJob);
                        }
                    }
                }


                break;
        }
        return $w;
    }
}