<?php
session_start();
$base_path = '../';
$page_title = 'Census Registration - Barangay Gumaoc East';
$header_title = 'Census Registration Form';
$header_subtitle = 'Barangay Population Census Data Collection';

include '../includes/header.php';
include '../includes/navigation.php';
?>

<div class="container">
  <div class="section">
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-error">
        <h4>Error</h4>
        <p><?php echo htmlspecialchars($_SESSION['error']); ?></p>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success">
        <h4>Success</h4>
        <p><?php echo htmlspecialchars($_SESSION['success']); ?></p>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form id="censusForm" class="registration-form" method="POST" action="process-census.php">

      <fieldset>
        <legend>Pangunahing Impormasyon</legend>
        
        <div class="form-grid">
          <div class="form-group">
            <label for="barangay">BARANGAY</label>
            <input type="text" id="barangay" name="barangay" value="Gumaoc East" readonly>
          </div>

          <div class="form-group">
            <label for="sitio">SITIO/POOK</label>
            <input type="text" id="sitio" name="sitio" value="BLOCK" readonly>
          </div>
        </div>

        <div class="form-group">
          <label for="headOfFamily">Pangalan ng Puno ng Pamilya *</label>
          <input type="text" id="headOfFamily" name="headOfFamily" required placeholder="Buong pangalan ng puno ng pamilya">
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="cellphone">Cellphone no.</label>
            <input type="tel" id="cellphone" name="cellphone" placeholder="09XXXXXXXXX" pattern="[0-9]{11}" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
          </div>

          <div class="form-group">
            <label for="houseNumber">Numero ng Bahay *</label>
            <input type="text" id="houseNumber" name="houseNumber" required placeholder="Numero ng bahay" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="interviewer">Pangalan ng Nakapanayam *</label>
            <input type="text" id="interviewer" name="interviewer" required placeholder="Buong pangalan ng nakapanayam">
          </div>

          <div class="form-group">
            <label for="interviewerTitle">Taga-panayam *</label>
            <input type="text" id="interviewerTitle" name="interviewerTitle" required placeholder="Posisyon/Tungkulin">
          </div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Mga Kasapi ng Pamilya</legend>
        <div class="table-wrapper">
          <!-- Main Table with Essential Info -->
          <div class="table-responsive">
            <table class="family-table">
              <thead>
                <tr>
                  <th>Pangalan</th>
                  <th>Relasyon sa Puno</th>
                  <th>Edad</th>
                  <th>Kasarian</th>
                  <th>Katayuang Sibil</th>
                  <th>Hanapbuhay</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="familyMembersBody">
                <tr class="family-member-row" data-member="0">
                  <td><input type="text" name="familyName[]" class="table-input" placeholder="Pangalan"></td>
                  <td><input type="text" name="familyRelation[]" class="table-input" placeholder="Relasyon"></td>
                  <td><input type="number" name="familyAge[]" class="table-input" placeholder="Edad" min="0" max="120"></td>
                  <td>
                    <select name="familyGender[]" class="table-input">
                      <option value="">Piliin</option>
                      <option value="Lalaki">Lalaki</option>
                      <option value="Babae">Babae</option>
                    </select>
                  </td>
                  <td>
                    <select name="familyCivilStatus[]" class="table-input">
                      <option value="">Piliin</option>
                      <option value="Single">Single</option>
                      <option value="Married">Married</option>
                      <option value="Widowed">Widowed</option>
                      <option value="Separated">Separated</option>
                    </select>
                  </td>
                  <td><input type="text" name="familyOccupation[]" class="table-input" placeholder="Hanapbuhay"></td>
                  <td>
                    <button type="button" class="btn-remove-simple" onclick="removeFamilyMember(this)">×</button>
                    <button type="button" class="btn-details-toggle" onclick="toggleMemberDetails(this)" data-target="0">⋯</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Additional Details Sections -->
          <div id="memberDetailsContainer">
            <div class="member-details" id="memberDetails-0" style="display: none;">
              <h6>Karagdagang Impormasyon - Kasapi #1</h6>
              <div class="details-grid">
                <div class="detail-group">
                  <label>Kapanganakan:</label>
                  <input type="date" name="familyBirthdate[]" class="detail-input">
                </div>
                <div class="detail-group">
                  <label>Lugar ng Kapanganakan:</label>
                  <input type="text" name="familyBirthplace[]" class="detail-input" placeholder="Lugar ng kapanganakan">
                </div>
                <div class="detail-group">
                  <label>Tagal ng Paninirahan sa Brgy:</label>
                  <input type="text" name="familyResidencyLength[]" class="detail-input" placeholder="Tagal ng paninirahan">
                </div>
                <div class="detail-group">
                  <label>Relihiyon:</label>
                  <input type="text" name="familyReligion[]" class="detail-input" placeholder="Relihiyon">
                </div>
                <div class="detail-group">
                  <label>Nag-aaral pa?</label>
                  <select name="familyStudying[]" class="detail-input">
                    <option value="">Piliin</option>
                    <option value="Oo">Oo</option>
                    <option value="Hindi">Hindi</option>
                  </select>
                </div>
                <div class="detail-group">
                  <label>Antas ng Pag-aaral:</label>
                  <input type="text" name="familyEducation[]" class="detail-input" placeholder="Antas ng pag-aaral">
                </div>
                <div class="detail-group">
                  <label>Kasanayan/Skills:</label>
                  <input type="text" name="familySkills[]" class="detail-input" placeholder="Kasanayan/Skills">
                </div>
                <div class="detail-group">
                  <label>Buwanang Kita:</label>
                  <input type="text" name="familyIncome[]" class="detail-input" placeholder="₱ 0.00">
                </div>
              </div>
            </div>
          </div>
          
          <button type="button" class="btn btn-add" onclick="addFamilyMember()">
            <span>➕</span> Magdagdag ng Kasapi ng Pamilya
          </button>
        </div>
      </fieldset>

      <fieldset>
        <legend>I. Pangkabuhayan</legend>
        
        <div class="subsection">
          <h4>A. Lupang Kinatatayuan</h4>
          <div class="checkbox-group">
            <label><input type="radio" name="landOwnership" value="Pag-aari" onchange="toggleOtherInput('landOwnership', 'landOwnershipOther')"> Pag-aari</label>
            <label><input type="radio" name="landOwnership" value="Inuupahan" onchange="toggleOtherInput('landOwnership', 'landOwnershipOther')"> Inuupahan</label>
            <label><input type="radio" name="landOwnership" value="Iba pa" onchange="toggleOtherInput('landOwnership', 'landOwnershipOther')"> Iba pa</label>
            <input type="text" name="landOwnershipOther" id="landOwnershipOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>B. Bahay na Tinitirhan</h4>
          <div class="checkbox-group">
            <label><input type="radio" name="houseOwnership" value="Pag-aari" onchange="toggleOtherInput('houseOwnership', 'houseOwnershipOther')"> Pag-aari</label>
            <label><input type="radio" name="houseOwnership" value="Umuupa" onchange="toggleOtherInput('houseOwnership', 'houseOwnershipOther')"> Umuupa</label>
            <label><input type="radio" name="houseOwnership" value="Iba pa" onchange="toggleOtherInput('houseOwnership', 'houseOwnershipOther')"> Iba pa</label>
            <input type="text" name="houseOwnershipOther" id="houseOwnershipOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>C. Lupahang Sakahan/Pinagyayaman</h4>
          <div class="checkbox-group">
            <label><input type="radio" name="farmland" value="Pag-aari"> Pag-aari</label>
            <label><input type="radio" name="farmland" value="Binubuwisan"> Binubuwisan</label>
            <label><input type="radio" name="farmland" value="Wala"> Wala</label>
          </div>
        </div>

        <div class="subsection">
          <h4>D. Pinagmumulan ng Enerhiya sa Pagluluto</h4>
          <div class="checkbox-group">
            <label><input type="radio" name="cookingEnergy" value="Gaas" onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')"> Gaas</label>
            <label><input type="radio" name="cookingEnergy" value="Kuryente" onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')"> Kuryente</label>
            <label><input type="radio" name="cookingEnergy" value="LPG" onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')"> LPG</label>
            <label><input type="radio" name="cookingEnergy" value="Kahoy" onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')"> Kahoy</label>
            <label><input type="radio" name="cookingEnergy" value="Iba pa" onchange="toggleOtherInput('cookingEnergy', 'cookingEnergyOther')"> Iba pa</label>
            <input type="text" name="cookingEnergyOther" id="cookingEnergyOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>E. Uri ng Palikuran</h4>
          <div class="checkbox-group">
            <label><input type="radio" name="toiletType" value="Flush" onchange="toggleOtherInput('toiletType', 'toiletTypeOther')"> Flush</label>
            <label><input type="radio" name="toiletType" value="De-buhos" onchange="toggleOtherInput('toiletType', 'toiletTypeOther')"> De-buhos</label>
            <label><input type="radio" name="toiletType" value="Hinuhukay/Balon" onchange="toggleOtherInput('toiletType', 'toiletTypeOther')"> Hinuhukay/Balon</label>
            <label><input type="radio" name="toiletType" value="Iba pa" onchange="toggleOtherInput('toiletType', 'toiletTypeOther')"> Iba pa</label>
            <input type="text" name="toiletTypeOther" id="toiletTypeOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>F. Pinagmumulan ng Elektrisidad</h4>
          <div class="checkbox-group">
            <label><input type="radio" name="electricitySource" value="Kuryente" onchange="toggleOtherInput('electricitySource', 'electricitySourceOther')"> Kuryente</label>
            <label><input type="radio" name="electricitySource" value="Gaas" onchange="toggleOtherInput('electricitySource', 'electricitySourceOther')"> Gaas</label>
            <label><input type="radio" name="electricitySource" value="Iba pa" onchange="toggleOtherInput('electricitySource', 'electricitySourceOther')"> Iba pa</label>
            <input type="text" name="electricitySourceOther" id="electricitySourceOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>G. Pinagkukunan ng Tubig</h4>
          <div class="checkbox-group">
            <label><input type="radio" name="waterSource" value="Poso Artesiyano" onchange="toggleOtherInput('waterSource', 'waterSourceOther')"> Poso Artesiyano</label>
            <label><input type="radio" name="waterSource" value="Water District" onchange="toggleOtherInput('waterSource', 'waterSourceOther')"> Water District</label>
            <label><input type="radio" name="waterSource" value="Nawasa" onchange="toggleOtherInput('waterSource', 'waterSourceOther')"> Nawasa</label>
            <label><input type="radio" name="waterSource" value="Iba pa" onchange="toggleOtherInput('waterSource', 'waterSourceOther')"> Iba pa</label>
            <input type="text" name="waterSourceOther" id="waterSourceOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>H. Pamamaraan ng Pagtatapon ng Basura</h4>
          <div class="checkbox-group">
            <label><input type="radio" name="wasteDisposal" value="Sinusunog" onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')"> Sinusunog</label>
            <label><input type="radio" name="wasteDisposal" value="Hukay na may takip" onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')"> Hukay na may takip</label>
            <label><input type="radio" name="wasteDisposal" value="Kinokolekta" onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')"> Kinokolekta</label>
            <label><input type="radio" name="wasteDisposal" value="Itinatapon kung saan" onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')"> Itinatapon kung saan</label>
            <label><input type="radio" name="wasteDisposal" value="Iba pa" onchange="toggleOtherInput('wasteDisposal', 'wasteDisposalOther')"> Iba pa</label>
            <input type="text" name="wasteDisposalOther" id="wasteDisposalOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>I. Kasangkapan sa Bahay</h4>
          <div class="checkbox-group">
            <label><input type="checkbox" name="appliances[]" value="Radyo/Stereo"> Radyo/Stereo</label>
            <label><input type="checkbox" name="appliances[]" value="Telebisyon"> Telebisyon (TV)</label>
            <label><input type="checkbox" name="appliances[]" value="Refrigerator"> Refrigerator</label>
            <label><input type="checkbox" name="appliances[]" value="Muwebles"> Muwebles (Furniture)</label>
          </div>
        </div>

        <div class="subsection">
          <h4>J. Transportasyon</h4>
          <div class="checkbox-group">
            <label><input type="checkbox" name="transportation[]" value="Sasakyan"> Sasakyan</label>
            <label><input type="checkbox" name="transportation[]" value="Jeep"> Jeep</label>
            <label><input type="checkbox" name="transportation[]" value="Kotse"> Kotse</label>
            <label><input type="checkbox" name="transportation[]" value="Tricycle"> Tricycle</label>
            <label><input type="checkbox" name="transportation[]" value="Truck"> Truck</label>
            <label><input type="checkbox" name="transportation[]" value="Motorsiklo"> Motorsiklo</label>
            <label><input type="checkbox" name="transportation[]" value="Iba pa" onchange="toggleCheckboxOther('transportation', 'transportationOther')"> Iba pa</label>
            <input type="text" name="transportationOther" id="transportationOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>K. Pang-Komersyo/Iba pang Pinagkakakitaan</h4>
          <div class="checkbox-group">
            <label><input type="checkbox" name="business[]" value="Sari-Sari Store"> Sari-Sari Store</label>
            <label><input type="checkbox" name="business[]" value="Patahian"> Patahian</label>
            <label><input type="checkbox" name="business[]" value="Rice Mill"> Rice Mill</label>
            <label><input type="checkbox" name="business[]" value="Iba pa" onchange="toggleCheckboxOther('business', 'businessOther')"> Iba pa</label>
            <input type="text" name="businessOther" id="businessOther" placeholder="Pakisulat kung iba pa" class="other-input" disabled>
          </div>
        </div>

        <div class="subsection">
          <h4>L. Gamit na Kontraseptibo</h4>
          <div class="checkbox-group">
            <label><input type="checkbox" name="contraceptive[]" value="Pills"> Pills</label>
            <label><input type="checkbox" name="contraceptive[]" value="IUD"> IUD</label>
            <label><input type="checkbox" name="contraceptive[]" value="Condom"> Condom</label>
            <label><input type="checkbox" name="contraceptive[]" value="Sterilization"> Sterilization (Ligation/VAS)</label>
            <label><input type="checkbox" name="contraceptive[]" value="INJ"> INJ</label>
            <label><input type="checkbox" name="contraceptive[]" value="NFP"> NFP (Natural F.P.)</label>
            <label><input type="checkbox" name="contraceptive[]" value="Wala"> Wala</label>
          </div>
        </div>
      </fieldset>

      <fieldset>
        <legend>II. Mga Kasambahay na may Kapansanan</legend>
        <div id="disabilitySection">
          <div class="disability-row">
            <div class="form-group">
              <label>Pangalan:</label>
              <input type="text" name="disabilityName[]" class="form-control" placeholder="Buong pangalan">
            </div>
            <div class="form-group">
              <label>Kapansanan:</label>
              <input type="text" name="disabilityType[]" class="form-control" placeholder="Uri ng kapansanan">
            </div>
            <button type="button" class="btn-remove-simple" onclick="removeDisabilityRow(this)">×</button>
          </div>
        </div>
        <button type="button" class="btn-plus" onclick="addDisabilityRow()">+</button>
      </fieldset>

      <fieldset>
        <legend>III. Mga Kasambahay na may Samahang Kinaaniban</legend>
        <div id="organizationSection">
          <div class="organization-row">
            <div class="form-group">
              <label>Pangalan:</label>
              <input type="text" name="organizationName[]" class="form-control" placeholder="Buong pangalan">
            </div>
            <div class="form-group">
              <label>Samahang Kinaaniban:</label>
              <input type="text" name="organizationType[]" class="form-control" placeholder="Pangalan ng samahan/organisasyon">
            </div>
            <button type="button" class="btn-remove-simple" onclick="removeOrganizationRow(this)">×</button>
          </div>
        </div>
        <button type="button" class="btn-plus" onclick="addOrganizationRow()">+</button>
      </fieldset>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">I-submit ang Census Form</button>
        <button type="reset" class="btn">I-clear ang Form</button>
      </div>
    </form>
  </div>
</div>

<script>
// Updated Family Member Management
let familyMemberCount = 1;

function addFamilyMember() {
  const tbody = document.getElementById('familyMembersBody');
  const detailsContainer = document.getElementById('memberDetailsContainer');
  
  const newRow = document.createElement('tr');
  newRow.className = 'family-member-row';
  newRow.setAttribute('data-member', familyMemberCount);
  
  newRow.innerHTML = `
    <td><input type="text" name="familyName[]" class="table-input" placeholder="Pangalan"></td>
    <td><input type="text" name="familyRelation[]" class="table-input" placeholder="Relasyon"></td>
    <td><input type="number" name="familyAge[]" class="table-input" placeholder="Edad" min="0" max="120"></td>
    <td>
      <select name="familyGender[]" class="table-input">
        <option value="">Piliin</option>
        <option value="Lalaki">Lalaki</option>
        <option value="Babae">Babae</option>
      </select>
    </td>
    <td>
      <select name="familyCivilStatus[]" class="table-input">
        <option value="">Piliin</option>
        <option value="Single">Single</option>
        <option value="Married">Married</option>
        <option value="Widowed">Widowed</option>
        <option value="Separated">Separated</option>
      </select>
    </td>
    <td><input type="text" name="familyOccupation[]" class="table-input" placeholder="Hanapbuhay"></td>
    <td>
      <button type="button" class="btn-remove-simple" onclick="removeFamilyMember(this)">×</button>
      <button type="button" class="btn-details-toggle" onclick="toggleMemberDetails(this)" data-target="${familyMemberCount}">⋯</button>
    </td>
  `;
  
  // Create corresponding details section
  const detailsDiv = document.createElement('div');
  detailsDiv.className = 'member-details';
  detailsDiv.id = `memberDetails-${familyMemberCount}`;
  detailsDiv.style.display = 'none';
  detailsDiv.innerHTML = `
    <h6>Karagdagang Impormasyon - Kasapi #${familyMemberCount + 1}</h6>
    <div class="details-grid">
      <div class="detail-group">
        <label>Kapanganakan:</label>
        <input type="date" name="familyBirthdate[]" class="detail-input">
      </div>
      <div class="detail-group">
        <label>Lugar ng Kapanganakan:</label>
        <input type="text" name="familyBirthplace[]" class="detail-input" placeholder="Lugar ng kapanganakan">
      </div>
      <div class="detail-group">
        <label>Tagal ng Paninirahan sa Brgy:</label>
        <input type="text" name="familyResidencyLength[]" class="detail-input" placeholder="Tagal ng paninirahan">
      </div>
      <div class="detail-group">
        <label>Relihiyon:</label>
        <input type="text" name="familyReligion[]" class="detail-input" placeholder="Relihiyon">
      </div>
      <div class="detail-group">
        <label>Nag-aaral pa?</label>
        <select name="familyStudying[]" class="detail-input">
          <option value="">Piliin</option>
          <option value="Oo">Oo</option>
          <option value="Hindi">Hindi</option>
        </select>
      </div>
      <div class="detail-group">
        <label>Antas ng Pag-aaral:</label>
        <input type="text" name="familyEducation[]" class="detail-input" placeholder="Antas ng pag-aaral">
      </div>
      <div class="detail-group">
        <label>Kasanayan/Skills:</label>
        <input type="text" name="familySkills[]" class="detail-input" placeholder="Kasanayan/Skills">
      </div>
      <div class="detail-group">
        <label>Buwanang Kita:</label>
        <input type="text" name="familyIncome[]" class="detail-input" placeholder="₱ 0.00">
      </div>
    </div>
  `;
  
  tbody.appendChild(newRow);
  detailsContainer.appendChild(detailsDiv);
  
  // Add animation to new row
  newRow.style.opacity = '0';
  newRow.style.transform = 'translateY(20px)';
  setTimeout(() => {
    newRow.style.transition = 'all 0.3s ease';
    newRow.style.opacity = '1';
    newRow.style.transform = 'translateY(0)';
  }, 10);
  
  familyMemberCount++;
}

// Remove Family Member from Table
function removeFamilyMember(button) {
  const row = button.closest('tr');
  const memberIndex = row.getAttribute('data-member');
  const detailsDiv = document.getElementById(`memberDetails-${memberIndex}`);
  
  row.style.transition = 'all 0.3s ease';
  row.style.opacity = '0';
  row.style.transform = 'translateX(-100%)';
  
  setTimeout(() => {
    // Remove both the row and its details section
    if (detailsDiv) {
      detailsDiv.remove();
    }
    row.remove();
    updateMemberNumbers();
  }, 300);
}

function toggleMemberDetails(button) {
  const target = button.getAttribute('data-target');
  const detailsDiv = document.getElementById(`memberDetails-${target}`);
  
  if (detailsDiv) {
    if (detailsDiv.style.display === 'none') {
      detailsDiv.style.display = 'block';
      button.textContent = '−';
      button.style.background = 'linear-gradient(135deg, #ff6b6b, #ee5a24)';
    } else {
      detailsDiv.style.display = 'none';
      button.textContent = '⋯';
      button.style.background = 'linear-gradient(135deg, #74b9ff, #0984e3)';
    }
  }
}

function updateMemberNumbers() {
  const detailsHeaders = document.querySelectorAll('.member-details h6');
  detailsHeaders.forEach((header, index) => {
    header.textContent = `Karagdagang Impormasyon - Kasapi #${index + 1}`;
  });
}

// Toggle Other Input for Radio Buttons
function toggleOtherInput(radioGroupName, otherInputId) {
  const radioButtons = document.querySelectorAll(`input[name="${radioGroupName}"]`);
  const otherInput = document.getElementById(otherInputId);
  
  let ibapaSelected = false;
  radioButtons.forEach(radio => {
    if (radio.checked && radio.value === 'Iba pa') {
      ibapaSelected = true;
    }
  });
  
  if (ibapaSelected) {
    otherInput.disabled = false;
    otherInput.style.opacity = '1';
    otherInput.style.background = '#fff';
    otherInput.focus();
  } else {
    otherInput.disabled = true;
    otherInput.style.opacity = '0.5';
    otherInput.style.background = '#f5f5f5';
    otherInput.value = '';
  }
}

// Toggle Other Input for Checkboxes (for sections that allow multiple selections)
function toggleCheckboxOther(checkboxGroupName, otherInputId) {
  const checkboxes = document.querySelectorAll(`input[name="${checkboxGroupName}[]"]`);
  const otherInput = document.getElementById(otherInputId);
  
  let ibapaSelected = false;
  checkboxes.forEach(checkbox => {
    if (checkbox.checked && checkbox.value === 'Iba pa') {
      ibapaSelected = true;
    }
  });
  
  if (ibapaSelected) {
    otherInput.disabled = false;
    otherInput.style.opacity = '1';
    otherInput.style.background = '#fff';
    otherInput.focus();
  } else {
    otherInput.disabled = true;
    otherInput.style.opacity = '0.5';
    otherInput.style.background = '#f5f5f5';
    otherInput.value = '';
  }
}

// Add Disability Row
function addDisabilityRow() {
  const container = document.getElementById('disabilitySection');
  const newRow = `
    <div class="disability-row">
      <div class="form-group">
        <label>Pangalan:</label>
        <input type="text" name="disabilityName[]" class="form-control" placeholder="Buong pangalan">
      </div>
      <div class="form-group">
        <label>Kapansanan:</label>
        <input type="text" name="disabilityType[]" class="form-control" placeholder="Uri ng kapansanan">
      </div>
      <button type="button" class="btn-remove-simple" onclick="removeDisabilityRow(this)">×</button>
    </div>
  `;
  container.insertAdjacentHTML('beforeend', newRow);
  
  // Add animation
  const newRowElement = container.lastElementChild;
  newRowElement.style.opacity = '0';
  newRowElement.style.transform = 'translateY(20px)';
  setTimeout(() => {
    newRowElement.style.transition = 'all 0.3s ease';
    newRowElement.style.opacity = '1';
    newRowElement.style.transform = 'translateY(0)';
  }, 10);
}

// Remove Disability Row
function removeDisabilityRow(button) {
  const row = button.closest('.disability-row');
  row.style.transition = 'all 0.3s ease';
  row.style.opacity = '0';
  row.style.transform = 'translateX(-100%)';
  setTimeout(() => {
    row.remove();
  }, 300);
}

// Add Organization Row
function addOrganizationRow() {
  const container = document.getElementById('organizationSection');
  const newRow = `
    <div class="organization-row">
      <div class="form-group">
        <label>Pangalan:</label>
        <input type="text" name="organizationName[]" class="form-control" placeholder="Buong pangalan">
      </div>
      <div class="form-group">
        <label>Samahang Kinaaniban:</label>
        <input type="text" name="organizationType[]" class="form-control" placeholder="Pangalan ng samahan/organisasyon">
      </div>
      <button type="button" class="btn-remove-simple" onclick="removeOrganizationRow(this)">×</button>
    </div>
  `;
  container.insertAdjacentHTML('beforeend', newRow);
  
  // Add animation
  const newRowElement = container.lastElementChild;
  newRowElement.style.opacity = '0';
  newRowElement.style.transform = 'translateY(20px)';
  setTimeout(() => {
    newRowElement.style.transition = 'all 0.3s ease';
    newRowElement.style.opacity = '1';
    newRowElement.style.transform = 'translateY(0)';
  }, 10);
}

// Remove Organization Row
function removeOrganizationRow(button) {
  const row = button.closest('.organization-row');
  row.style.transition = 'all 0.3s ease';
  row.style.opacity = '0';
  row.style.transform = 'translateX(-100%)';
  setTimeout(() => {
    row.remove();
  }, 300);
}

// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('censusForm');
  
  // Add smooth scrolling for form navigation
  const fieldsets = document.querySelectorAll('fieldset');
  fieldsets.forEach((fieldset, index) => {
    fieldset.style.animationDelay = `${index * 0.1}s`;
  });
  
  // Enhanced input focus effects
  const inputs = document.querySelectorAll('input, select, textarea');
  inputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.closest('.form-group')?.classList.add('focused');
    });
    
    input.addEventListener('blur', function() {
      this.closest('.form-group')?.classList.remove('focused');
    });
  });
  
  // Format currency inputs
  document.addEventListener('input', function(e) {
    if (e.target.name === 'familyIncome[]') {
      let value = e.target.value.replace(/[^\d]/g, '');
      if (value) {
        e.target.value = '₱ ' + parseInt(value).toLocaleString();
      }
    }
  });
});
</script>

<style>
/* Global Styles */
* {
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: #2c3e50;
  background: url('../background.jpg') no-repeat center center fixed;
  background-size: cover;
  min-height: 100vh;
  margin: 0;
  padding: 0;
}

body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.85);
  z-index: -1;
}

/* Container Styles */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 15px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  margin-top: 20px;
  margin-bottom: 20px;
}

.section {
  margin-bottom: 25px;
}

/* Form Wrapper */
.registration-form {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Fieldset Styles */
fieldset {
  border: none;
  background: linear-gradient(145deg, #f8f9fa, #e9ecef);
  margin: 0;
  padding: 40px 35px;
  position: relative;
  border-bottom: 1px solid #e9ecef;
}

fieldset:last-of-type {
  border-bottom: none;
}

fieldset::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  background: linear-gradient(45deg, #28a745, #20c997);
}

legend {
  font-weight: 700;
  color: #2c3e50;
  font-size: 1.5rem;
  margin-bottom: 25px;
  padding: 10px 20px;
  background: linear-gradient(45deg, #28a745, #20c997);
  color: white;
  border-radius: 50px;
  display: inline-block;
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 1rem;
}

/* Form Elements */
.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  margin-bottom: 10px;
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input[type="text"],
.form-group input[type="tel"],
.form-group input[type="email"],
.form-group input[type="number"],
.form-group input[type="date"],
.form-group select,
.form-group textarea,
.form-control {
  width: 100%;
  padding: 15px 20px;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-size: 16px;
  transition: all 0.3s ease;
  background: #fff;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus,
.form-control:focus {
  outline: none;
  border-color: #28a745;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
  transform: translateY(-2px);
}

/* Grid Layout for Form Groups */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 25px;
}

/* Table Wrapper */
.table-wrapper {
  background: #fff;
  border-radius: 16px;
  padding: 25px;
  margin: 20px 0;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
  border: 1px solid #e9ecef;
}

/* Table Styles */
.table-responsive {
  overflow-x: auto;
  margin-bottom: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.family-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
}

.family-table th {
  background: linear-gradient(45deg, #28a745, #20c997);
  color: white;
  font-weight: 600;
  font-size: 11px;
  padding: 15px 8px;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: none;
  position: sticky;
  top: 0;
  z-index: 10;
}

.family-table th:first-child {
  border-top-left-radius: 12px;
}

.family-table th:last-child {
  border-top-right-radius: 12px;
}

.family-table td {
  padding: 12px 8px;
  border-bottom: 1px solid #f1f3f4;
  border-right: 1px solid #f1f3f4;
  vertical-align: middle;
}

.family-table td:last-child {
  border-right: none;
  text-align: center;
}

.family-table tr:hover {
  background-color: rgba(40, 167, 69, 0.05);
}

.table-input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  font-size: 12px;
  transition: all 0.3s ease;
  background: #fff;
  min-width: 90px;
}

.table-input:focus {
  outline: none;
  border-color: #28a745;
  box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.1);
}

/* Simplified Remove Button */
.btn-remove-simple {
  background: #dc3545;
  color: white;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-remove-simple:hover {
  background: #c82333;
  transform: scale(1.1);
}

/* Details Toggle Button */
.btn-details-toggle {
  background: linear-gradient(135deg, #74b9ff, #0984e3);
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  font-size: 12px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-left: 5px;
  transition: all 0.3s ease;
}

.btn-details-toggle:hover {
  transform: scale(1.1);
  box-shadow: 0 2px 10px rgba(116, 185, 255, 0.3);
}

/* Member Details Section */
.member-details {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 8px;
  padding: 15px;
  margin: 10px 0;
  border-left: 4px solid #28a745;
  animation: slideDown 0.3s ease-out;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.member-details h6 {
  margin: 0 0 15px 0;
  color: #28a745;
  font-weight: 600;
  font-size: 14px;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px;
}

.detail-group {
  display: flex;
  flex-direction: column;
}

.detail-group label {
  font-size: 12px;
  font-weight: 600;
  color: #555;
  margin-bottom: 5px;
}

.detail-input {
  padding: 8px 10px;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  font-size: 13px;
  transition: all 0.3s ease;
  background: #fff;
}

.detail-input:focus {
  border-color: #28a745;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
  outline: none;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Simple Plus Button */
.btn-plus {
  background: #28a745;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 15px auto;
  box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
}

.btn-plus:hover {
  background: #218838;
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
}

/* Subsection Styles */
.subsection {
  margin-bottom: 30px;
  padding: 25px;
  background: #fff;
  border-radius: 16px;
  border: 2px solid #f1f3f4;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.subsection::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(45deg, #28a745, #20c997);
}

.subsection:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-color: #28a745;
}

.subsection h4 {
  color: #2c3e50;
  margin-bottom: 20px;
  font-size: 1.2rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding-bottom: 10px;
  border-bottom: 2px solid #e9ecef;
}

/* Checkbox Groups */
.checkbox-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  align-items: start;
  margin-top: 15px;
}

.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
  cursor: pointer;
  font-weight: 500;
  padding: 12px 15px;
  border-radius: 10px;
  transition: all 0.3s ease;
  background: #f8f9fa;
  border: 2px solid transparent;
}

.checkbox-group label:hover {
  background: rgba(40, 167, 69, 0.1);
  border-color: #28a745;
  transform: translateY(-1px);
}

.checkbox-group input[type="checkbox"] {
  margin: 0;
  width: 20px;
  height: 20px;
  accent-color: #28a745;
  cursor: pointer;
}

.checkbox-group input[type="radio"] {
  margin: 0;
  width: 20px;
  height: 20px;
  accent-color: #28a745;
  cursor: pointer;
}

.other-input {
  margin-top: 15px;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  min-width: 250px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.other-input:focus {
  outline: none;
  border-color: #28a745;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.other-input:disabled {
  background: #f5f5f5;
  color: #999;
  cursor: not-allowed;
  opacity: 0.5;
  border-color: #ddd;
}

.other-input:disabled::placeholder {
  color: #bbb;
}

/* Dynamic Rows */
.disability-row,
.organization-row {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 20px;
  align-items: end;
  padding: 25px;
  background: #fff;
  border: 2px solid #e9ecef;
  border-radius: 16px;
  margin-bottom: 15px;
  transition: all 0.3s ease;
}

.disability-row:hover,
.organization-row:hover {
  border-color: #28a745;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Buttons */
.btn {
  padding: 15px 30px;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  position: relative;
  overflow: hidden;
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s;
}

.btn:hover::before {
  left: 100%;
}

.btn-primary {
  background: linear-gradient(45deg, #28a745, #20c997);
  color: white;
  box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 35px rgba(40, 167, 69, 0.4);
}

.btn-add {
  background: linear-gradient(45deg, #007bff, #6610f2);
  color: white;
  margin-top: 15px;
  box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
}

.btn-add:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 35px rgba(0, 123, 255, 0.4);
}

.btn-remove {
  background: linear-gradient(45deg, #dc3545, #fd7e14);
  color: white;
  padding: 10px 15px;
  font-size: 16px;
  font-weight: bold;
  min-width: auto;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-remove:hover {
  transform: translateY(-2px) scale(1.1);
  box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 20px;
  justify-content: center;
  margin-top: 40px;
  padding: 30px;
  background: linear-gradient(145deg, #f8f9fa, #e9ecef);
  border-radius: 16px;
}

.form-actions .btn {
  min-width: 200px;
  font-size: 18px;
  padding: 18px 35px;
}

.form-actions .btn[type="reset"] {
  background: linear-gradient(45deg, #6c757d, #495057);
  color: white;
  box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
}

.form-actions .btn[type="reset"]:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 35px rgba(108, 117, 125, 0.4);
}

/* Readonly Inputs */
input[readonly] {
  background: linear-gradient(145deg, #e9ecef, #f8f9fa);
  cursor: not-allowed;
  border-color: #ced4da;
  color: #6c757d;
  font-weight: 600;
}

/* Loading Animation */
.btn:active {
  transform: scale(0.98);
}

/* Responsive Design */
@media (max-width: 1200px) {
  .container {
    max-width: 95%;
    padding: 20px 15px;
  }
  
  .family-table {
    min-width: 1200px;
  }
}

@media (max-width: 768px) {
  .container {
    margin: 10px;
    padding: 20px 15px;
    border-radius: 16px;
  }
  
  fieldset {
    padding: 30px 20px;
  }
  
  legend {
    font-size: 0.9rem;
    padding: 8px 16px;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .checkbox-group {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .checkbox-group label {
    padding: 15px;
  }
  
  .disability-row,
  .organization-row {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 15px;
  }
  
  .form-actions .btn {
    width: 100%;
    margin: 0;
  }
  
  .other-input {
    margin-left: 0;
    margin-top: 15px;
    width: 100%;
    min-width: auto;
  }
  
  .table-wrapper {
    padding: 15px;
    margin: 15px 0;
  }
  
  .family-table {
    font-size: 12px;
  }
  
  .family-table th,
  .family-table td {
    padding: 8px 6px;
    font-size: 11px;
  }
  
  .table-input {
    min-width: 60px;
    font-size: 10px;
    padding: 4px 6px;
  }
  
  .btn-remove-simple,
  .btn-details-toggle {
    width: 20px;
    height: 20px;
    font-size: 11px;
    margin: 1px;
  }
  
  .member-details {
    padding: 12px;
    margin: 8px 0;
  }
  
  .member-details h6 {
    font-size: 13px;
    margin-bottom: 10px;
  }
  
  .details-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .detail-input {
    padding: 7px 9px;
    font-size: 12px;
  }
}

@media (max-width: 576px) {
  .family-table {
    font-size: 10px;
  }
  
  .family-table th,
  .family-table td {
    padding: 6px 4px;
    font-size: 10px;
  }
  
  .table-input {
    font-size: 9px;
    padding: 3px 5px;
    min-width: 50px;
  }
  
  .btn-remove-simple,
  .btn-details-toggle {
    width: 18px;
    height: 18px;
    font-size: 10px;
  }
  
  .member-details {
    padding: 10px;
    margin: 5px 0;
  }
  
  .member-details h6 {
    font-size: 12px;
    margin-bottom: 8px;
  }
  
  .detail-input {
    padding: 6px 8px;
    font-size: 11px;
  }
}
  
  .subsection {
    padding: 20px;
  }
  
  .btn {
    padding: 12px 20px;
    font-size: 14px;
  }
  
  .btn-remove-simple {
    width: 20px;
    height: 20px;
    font-size: 12px;
  }
  
  .btn-plus {
    width: 35px;
    height: 35px;
    font-size: 18px;
  }
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.registration-form {
  animation: fadeInUp 0.6s ease-out;
}

fieldset {
  animation: fadeInUp 0.6s ease-out;
  animation-fill-mode: both;
}

fieldset:nth-child(1) { animation-delay: 0.1s; }
fieldset:nth-child(2) { animation-delay: 0.2s; }
fieldset:nth-child(3) { animation-delay: 0.3s; }
fieldset:nth-child(4) { animation-delay: 0.4s; }
fieldset:nth-child(5) { animation-delay: 0.5s; }

/* Scroll to top behavior */
html {
  scroll-behavior: smooth;
}
</style>

<?php include '../includes/footer.php'; ?>
