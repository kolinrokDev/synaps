<?php

namespace Drupal\projects_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a block showing the 3 latest projects.
 *
 * @Block(
 *   id = "latest_projects_block",
 *   admin_label = @Translation("Latest Projects Block"),
 * )
 */
class LatestProjectsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'project')
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->accessCheck(TRUE);

    $nids = $query->execute();

    if (!$nids) {
      return ['#markup' => 'Нет проектов'];
    }

    $nodes = Node::loadMultiple($nids);

    $html = '<div class="latest-projects-block"><h2 class="block-title">Последние проекты</h2><div class="projects-wrapper">';

    foreach ($nodes as $node) {


      $img_url = '';
      if ($node->hasField('field_image') && !$node->get('field_image')->isEmpty()) {
        $file = $node->get('field_image')->entity;
        if ($file) {
          $img_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }


      $description = '';
      if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
        $description = $node->get('body')->summary ?: $node->get('body')->value;
      }


      $date = '';
      if ($node->hasField('field_end_date') && !$node->get('field_end_date')->isEmpty()) {
        $date = date('d.m.Y', strtotime($node->get('field_end_date')->value));
      }

      $html .= '
        <div class="project-item">
            '.($img_url ? '<img class="project-img" src="'.$img_url.'" alt="project image" />' : '').'
            <h3 class="project-title">'.$node->label().'</h3>
            <div class="project-desc">'. $description .'</div>
            '.($date ? '<div class="project-date">Дата окончания: '.$date.'</div>' : '').'
        </div>
      ';
    }

    $html .= '</div></div>';

    return [
      '#markup' => $html,
      '#attached' => [
        'library' => [
          'projects_module/projects_module_styles',
        ],
      ],
    ];
  }

  public function getCacheMaxAge() {
    return 0;
  }

  public function getCacheContexts() {
    return ['url'];
  }

  public function getCacheTags() {
    return ['node_list:project'];
  }
}
