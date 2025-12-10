<?php


namespace Drupal\projects_module\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Response;

class ProjectsController extends ControllerBase
{


  /**
   * Возвращает JSON со списком опубликованных проектов.
   */
  public function getProjects() {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'project')
      ->sort('created', 'DESC')
      ->accessCheck(TRUE);

    $nids = $query->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

    $data = [];
    foreach ($nodes as $node) {
      $image_url = null;
      if ($node->hasField('field_image') && !$node->get('field_image')->isEmpty()) {
        $file = $node->get('field_image')->entity;
        if ($file) {
          $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      $end_date = $node->hasField('field_end_date') && !$node->get('field_end_date')->isEmpty()
        ? $node->get('field_end_date')->value
        : '';

      $description_raw = $node->hasField('body') && !$node->get('body')->isEmpty()
        ? $node->get('body')->value
        : '';


      $description = trim(strip_tags($description_raw));


      $created_date = date('Y-m-d', $node->getCreatedTime());

      $data[] = [
        'id' => $node->id(),
        'title' => $node->label(),
        'description' => $description ?: '',
        'image' => $image_url ?: '',
        'end_date' => $end_date ?: '',
        'created' => $created_date,
        'url' => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
      ];
    }

    return new Response(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  }
}
